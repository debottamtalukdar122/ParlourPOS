<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Support\Flash;
use App\Support\Request;
use App\Support\Response;
use App\Support\View;
use PDO;
use RuntimeException;

final class OperationsController extends Controller
{
    public function __construct(View $view, private readonly PDO $pdo) { parent::__construct($view); }
    private function guard(): ?Response { return AuthService::userId() ? null : Response::redirect('/login'); }
    private function code(string $prefix): string { return $prefix . '-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))); }

    public function purchases(Request $request): Response
    {
        if ($response = $this->permitted($this->pdo, 'purchases.manage')) return $response;
        return $this->render('operations.purchases', ['title' => 'Purchases & Stock In', 'purchases' => $this->pdo->query('SELECT p.*,s.name supplier_name FROM purchases p JOIN suppliers s ON s.id=p.supplier_id ORDER BY p.id DESC LIMIT 100')->fetchAll(), 'suppliers' => $this->pdo->query('SELECT id,name FROM suppliers WHERE is_active=1 ORDER BY name')->fetchAll(), 'products' => $this->pdo->query('SELECT id,name,purchase_price,tax_rate FROM products WHERE is_active=1 ORDER BY name')->fetchAll(), 'csrf' => Flash::csrf(), 'flash' => Flash::get()]);
    }

    public function createPurchase(Request $request): Response
    {
        if ($response = $this->permitted($this->pdo, 'purchases.manage')) return $response;
        if (!Flash::validCsrf($request->input('_token'))) return $this->back('/purchases', 'Invalid request.');
        $lines = json_decode((string)$request->input('lines_json'), true);
        $supplier = (int)$request->input('supplier_id');
        if (!$supplier || !is_array($lines) || $lines === []) return $this->back('/purchases', 'Choose a supplier and at least one product.');
        $this->pdo->beginTransaction();
        try {
            $subtotal = 0.0; $tax = 0.0; $prepared = [];
            foreach ($lines as $line) {
                $stmt = $this->pdo->prepare('SELECT * FROM products WHERE id=? AND is_active=1 FOR UPDATE'); $stmt->execute([(int)($line['id'] ?? 0)]); $product = $stmt->fetch();
                if (!$product) throw new RuntimeException('A selected product is unavailable.');
                $quantity = max(.001, (float)($line['quantity'] ?? 0)); $price = max(0, (float)($line['price'] ?? $product['purchase_price'])); $net = round($quantity * $price, 2); $lineTax = round($net * ((float)$product['tax_rate'] / 100), 2);
                $prepared[] = compact('product','quantity','price','net','lineTax'); $subtotal += $net; $tax += $lineTax;
            }
            $this->pdo->prepare('INSERT INTO purchases(purchase_number,supplier_id,supplier_invoice_no,purchase_date,subtotal,tax_total,grand_total,payment_status,created_by) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$this->code('PUR'), $supplier, trim((string)$request->input('supplier_invoice_no')) ?: null, $request->input('purchase_date') ?: date('Y-m-d'), $subtotal, $tax, $subtotal + $tax, $request->input('payment_status') ?: 'unpaid', AuthService::userId()]);
            $purchaseId = (int)$this->pdo->lastInsertId();
            foreach ($prepared as $row) {
                $this->pdo->prepare('INSERT INTO purchase_items(purchase_id,product_id,quantity,unit_price,tax_rate,line_total) VALUES(?,?,?,?,?,?)')->execute([$purchaseId,$row['product']['id'],$row['quantity'],$row['price'],$row['product']['tax_rate'],$row['net'] + $row['lineTax']]);
                $before = (float)$row['product']['current_stock']; $after = $before + $row['quantity'];
                $this->pdo->prepare('UPDATE products SET current_stock=?,purchase_price=? WHERE id=?')->execute([$after,$row['price'],$row['product']['id']]);
                $this->pdo->prepare("INSERT INTO stock_movements(product_id,movement_type,quantity,before_quantity,after_quantity,reference_type,reference_id,reason,created_by) VALUES(?,'purchase',?,?,?,?,?,?,?)")->execute([$row['product']['id'],$row['quantity'],$before,$after,'purchase',$purchaseId,'Purchase receipt',AuthService::userId()]);
            }
            $this->pdo->commit();

            try {
                \App\Services\AuditService::log($this->pdo, 'created', 'purchases', $purchaseId, [
                    'supplier_id' => $supplier,
                    'grand_total' => $subtotal + $tax,
                    'items_count' => count($prepared),
                ]);
            } catch (\Throwable) {}

            return $this->back('/purchases', 'Purchase completed and stock updated.', 'success');
        } catch (\Throwable $e) { $this->pdo->rollBack(); return $this->back('/purchases', $e->getMessage()); }
    }


    public function reports(Request $request): Response
    {
        if ($response = $this->permitted($this->pdo, 'reports.view')) return $response; $from=$request->input('from') ?: date('Y-m-01'); $to=$request->input('to') ?: date('Y-m-d');
        $dateParams=[$from,$to];
        $summary=['sales'=>(float)$this->scalar("SELECT COALESCE(SUM(grand_total),0) FROM invoices WHERE status='finalized' AND DATE(created_at) BETWEEN ? AND ?",$dateParams),'expenses'=>(float)$this->scalar('SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date BETWEEN ? AND ?',$dateParams),'payments'=>(float)$this->scalar("SELECT COALESCE(SUM(p.amount),0) FROM payments p WHERE p.status='captured' AND DATE(p.paid_at) BETWEEN ? AND ?",$dateParams),'commissions'=>(float)$this->scalar('SELECT COALESCE(SUM(commission_amount),0) FROM commissions WHERE DATE(created_at) BETWEEN ? AND ?',$dateParams)]; $summary['profit']=$summary['sales']-$summary['expenses']-$summary['commissions'];
        $daily=$this->query("SELECT DATE(created_at) day,SUM(grand_total) total FROM invoices WHERE status='finalized' AND DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY day",$dateParams);
        return $this->render('operations.reports',['title'=>'Reports','from'=>$from,'to'=>$to,'summary'=>$summary,'daily'=>$daily,'payments'=>$this->query("SELECT pm.name,SUM(p.amount) total FROM payments p JOIN payment_methods pm ON pm.id=p.payment_method_id WHERE p.status='captured' AND DATE(p.paid_at) BETWEEN ? AND ? GROUP BY pm.id,pm.name",$dateParams),'staff'=>$this->query("SELECT s.name,SUM(ii.line_total) total FROM invoice_items ii JOIN invoices i ON i.id=ii.invoice_id JOIN staff s ON s.id=ii.staff_id WHERE i.status='finalized' AND DATE(i.created_at) BETWEEN ? AND ? GROUP BY s.id,s.name ORDER BY total DESC",$dateParams),'lowStock'=>$this->pdo->query('SELECT name,current_stock,minimum_stock FROM products WHERE is_active=1 AND current_stock<=minimum_stock ORDER BY current_stock')->fetchAll(),'csrf'=>Flash::csrf(),'flash'=>Flash::get()]);
    }
    private function scalar(string $sql,array $params): mixed {$stmt=$this->pdo->prepare($sql);$stmt->execute($params);return $stmt->fetchColumn();}
    private function query(string $sql,array $params): array {$stmt=$this->pdo->prepare($sql);$stmt->execute($params);return $stmt->fetchAll();}
    private function back(string $path,string $message,string $type='danger'): Response { Flash::set($type,$message); return Response::redirect($path); }
}
