<?php
declare(strict_types=1);
namespace App\Services;
use PDO;
use RuntimeException;
final class BillingService {
    public function __construct(private readonly PDO $pdo) {}
    /** @param list<array{type:string,id:int,quantity:float,staff_id?:int}> $lines */
    public function finalize(int $customerId, array $lines, int $paymentMethodId, float $paidAmount, ?int $userId, float $discount = 0): int {
        if ($lines === []) throw new RuntimeException('Add at least one item.');
        $customer = $this->pdo->prepare('SELECT id FROM customers WHERE id=? AND is_active=1'); $customer->execute([$customerId]);
        if (!$customer->fetchColumn()) throw new RuntimeException('Please select an active customer.');
        $method = $this->pdo->prepare('SELECT id FROM payment_methods WHERE id=? AND is_active=1'); $method->execute([$paymentMethodId]);
        if (!$method->fetchColumn()) throw new RuntimeException('Please select a valid payment method.');
        $this->pdo->beginTransaction();
        try {
            $prepared=[]; $subtotal=0.0; $tax=0.0;
            foreach ($lines as $line) {
                if (!isset($line['type'], $line['id']) || !in_array($line['type'], ['service','product'], true)) throw new RuntimeException('An invoice item is invalid.');
                $isProduct=$line['type']==='product'; $table=$isProduct?'products':'services';
                $stmt=$this->pdo->prepare("SELECT * FROM {$table} WHERE id=? AND is_active=1" . ($isProduct?' FOR UPDATE':'')); $stmt->execute([$line['id']]); $item=$stmt->fetch();
                if (!$item) throw new RuntimeException('An item is unavailable.'); $qty=max(0.001,(float)$line['quantity']);
                if ($isProduct && (float)$item['current_stock'] < $qty) throw new RuntimeException("Insufficient stock for {$item['name']}.");
                $unit=(float)($isProduct?$item['selling_price']:$item['price']); $lineNet=round($qty*$unit,2); $lineTax=round($lineNet*((float)$item['tax_rate']/100),2);
                if (!$isProduct && !empty($line['staff_id'])) { $staffCheck=$this->pdo->prepare('SELECT id FROM staff WHERE id=? AND is_active=1'); $staffCheck->execute([(int)$line['staff_id']]); if (!$staffCheck->fetchColumn()) throw new RuntimeException('The selected staff member is unavailable.'); }
                $prepared[]=compact('line','item','isProduct','qty','unit','lineNet','lineTax'); $subtotal+=$lineNet; $tax+=$lineTax;
            }
            if ($discount < 0 || $discount > $subtotal) throw new RuntimeException('Discount amount is invalid.');
            if ($discount > 0 && $subtotal > 0) { $tax=0.0; foreach ($prepared as &$p) { $share=$p['lineNet']/$subtotal; $p['lineTax']=round(($p['lineNet']-($discount*$share))*((float)$p['item']['tax_rate']/100),2); $tax+=$p['lineTax']; } unset($p); }
            $grand=round($subtotal-$discount+$tax,2); if ($paidAmount < 0 || $paidAmount > $grand) throw new RuntimeException('Payment amount is invalid.');
            $number='INV-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(3)));
            $stmt=$this->pdo->prepare('INSERT INTO invoices (invoice_number,customer_id,cashier_id,subtotal,discount_total,tax_total,grand_total,paid_total,balance_total) VALUES (?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$number,$customerId,$userId,$subtotal,$discount,$tax,$grand,$paidAmount,$grand-$paidAmount]); $invoiceId=(int)$this->pdo->lastInsertId();
            foreach ($prepared as $index=>$row) {
                $p=$row; $this->pdo->prepare('INSERT INTO invoice_items (invoice_id,line_no,item_type,service_id,product_id,staff_id,description,quantity,unit_price,tax_rate,tax_amount,line_total) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')->execute([$invoiceId,$index+1,$p['isProduct']?'product':'service',$p['isProduct']?null:$p['item']['id'],$p['isProduct']?$p['item']['id']:null,$p['line']['staff_id']??null,$p['item']['name'],$p['qty'],$p['unit'],$p['item']['tax_rate'],$p['lineTax'],$p['lineNet']+$p['lineTax']]);
                $invoiceItemId=(int)$this->pdo->lastInsertId();
                if (!$p['isProduct'] && !empty($p['line']['staff_id'])) { $staff=$this->pdo->prepare('SELECT commission_type,commission_value FROM staff WHERE id=?');$staff->execute([(int)$p['line']['staff_id']]);$staff=$staff->fetch();if($staff){$amount=$staff['commission_type']==='percentage'?round(($p['lineNet']*((float)$staff['commission_value']))/100,2):(float)$staff['commission_value'];$this->pdo->prepare('INSERT INTO commissions(invoice_item_id,staff_id,invoice_id,commission_type,commission_rate,commission_amount) VALUES(?,?,?,?,?,?)')->execute([$invoiceItemId,$p['line']['staff_id'],$invoiceId,$staff['commission_type'],$staff['commission_value'],$amount]);} }
                if ($p['isProduct']) { $before=(float)$p['item']['current_stock']; $after=$before-$p['qty']; $this->pdo->prepare('UPDATE products SET current_stock=? WHERE id=?')->execute([$after,$p['item']['id']]); $this->pdo->prepare("INSERT INTO stock_movements (product_id,movement_type,quantity,before_quantity,after_quantity,reference_type,reference_id,created_by) VALUES (?,'sale',?,?,?,?,?,?)")->execute([$p['item']['id'],-$p['qty'],$before,$after,'invoice',$invoiceId,$userId]); }
            }
            if ($paidAmount > 0) $this->pdo->prepare("INSERT INTO payments (payment_code,invoice_id,payment_method_id,amount,received_by,paid_at) VALUES (?,?,?,?,?,NOW())")->execute(['PAY-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(3))),$invoiceId,$paymentMethodId,$paidAmount,$userId]);
            AuditService::log($this->pdo,'created','invoice',$invoiceId,['invoice_number'=>$number,'grand_total'=>$grand]);
            $this->pdo->commit(); return $invoiceId;
        } catch (\Throwable $e) { if ($this->pdo->inTransaction()) $this->pdo->rollBack(); throw $e; }
    }
}
