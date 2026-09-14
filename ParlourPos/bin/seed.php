<?php

declare(strict_types=1);

use App\Services\BillingService;
use App\Support\Database;

require dirname(__DIR__) . '/bootstrap/app.php';

/**
 * Creates one realistic, internally consistent salon demo dataset. It never
 * deletes data and is safe to run again: after the first successful run it
 * simply reports that the demo data already exists.
 *
 * Supply DEMO_ADMIN_PASSWORD through the environment (or enter it securely at
 * the prompt). The password is only ever stored as a password_hash.
 */
$pdo = Database::connection($config['database']);
$marker = 'velora_demo_seed_v1';
if ($pdo->prepare('SELECT metadata_value FROM system_metadata WHERE metadata_key=?')->execute([$marker])) {
    $check = $pdo->prepare('SELECT metadata_value FROM system_metadata WHERE metadata_key=?');
    $check->execute([$marker]);
    if ($check->fetchColumn()) { fwrite(STDOUT, "Demo data is already installed. No records were changed.\n"); exit(0); }
}

$password = getenv('DEMO_ADMIN_PASSWORD') ?: '';
if ($password === '' && defined('STDIN')) {
    fwrite(STDOUT, 'Demo admin password (minimum 8 characters): ');
    $password = trim((string) fgets(STDIN));
}
if (strlen($password) < 8) {
    fwrite(STDERR, "Set DEMO_ADMIN_PASSWORD to a password with at least 8 characters.\n");
    exit(1);
}

function id(PDO $pdo, string $table, string $column, string $value): int {
    $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE {$column}=?");
    $stmt->execute([$value]);
    return (int) $stmt->fetchColumn();
}
function insert(PDO $pdo, string $sql, array $values): int { $pdo->prepare($sql)->execute($values); return (int) $pdo->lastInsertId(); }

try {
    $pdo->beginTransaction();
    $roleId = id($pdo, 'roles', 'name', 'Super Admin');
    $email = 'admin@veloraparlour.demo';
    $admin = id($pdo, 'users', 'email', $email);
    if (!$admin) $admin = insert($pdo, 'INSERT INTO users(name,email,password_hash,role_id,is_active) VALUES(?,?,?,?,1)', ['Ananya Mehta', $email, password_hash($password, PASSWORD_DEFAULT), $roleId]);

    $serviceCategories = ['Hair Studio', 'Skin & Facial', 'Nails', 'Makeup', 'Wellness'];
    foreach ($serviceCategories as $name) if (!id($pdo, 'service_categories', 'name', $name)) insert($pdo, 'INSERT INTO service_categories(name) VALUES(?)', [$name]);
    $productCategories = ['Hair Care', 'Skin Care', 'Colour & Styling', 'Nail Care'];
    foreach ($productCategories as $name) if (!id($pdo, 'product_categories', 'name', $name)) insert($pdo, 'INSERT INTO product_categories(name) VALUES(?)', [$name]);
    foreach (['Rent', 'Utilities', 'Cleaning', 'Maintenance', 'Marketing'] as $name) if (!id($pdo, 'expense_categories', 'name', $name)) insert($pdo, 'INSERT INTO expense_categories(name) VALUES(?)', [$name]);

    $staff = [
        ['STF-DEMO-01','Priya Sharma','9876543210','Senior Hair Stylist',12], ['STF-DEMO-02','Neha Kapoor','9876543211','Skin Therapist',10],
        ['STF-DEMO-03','Riya Verma','9876543212','Nail Artist',10], ['STF-DEMO-04','Kavya Iyer','9876543213','Makeup Artist',15], ['STF-DEMO-05','Meera Nair','9876543214','Salon Associate',8],
    ];
    foreach ($staff as [$code,$name,$mobile,$designation,$commission]) insert($pdo, 'INSERT INTO staff(staff_code,name,mobile,designation,commission_type,commission_value) VALUES(?,?,?,?,\'percentage\',?)', [$code,$name,$mobile,$designation,$commission]);
    $staffIds = $pdo->query("SELECT id FROM staff WHERE staff_code LIKE 'STF-DEMO-%' ORDER BY staff_code")->fetchAll(PDO::FETCH_COLUMN);

    $suppliers = [['SUP-DEMO-01','Glow Essentials Distributors','9898981001','Glow Essentials'],['SUP-DEMO-02','Luxe Beauty Supply','9898981002','Luxe Beauty Supply'],['SUP-DEMO-03','Professional Salon Mart','9898981003','Salon Mart India']];
    foreach ($suppliers as $row) insert($pdo, 'INSERT INTO suppliers(supplier_code,name,mobile,company) VALUES(?,?,?,?)', $row);
    $supplierIds = $pdo->query("SELECT id FROM suppliers WHERE supplier_code LIKE 'SUP-DEMO-%' ORDER BY supplier_code")->fetchAll(PDO::FETCH_COLUMN);

    $services = [
        ['SVC-DEMO-01','Hair Studio','Signature Haircut',45,650,5],['SVC-DEMO-02','Hair Studio','Hair Spa Ritual',60,1450,5],['SVC-DEMO-03','Hair Studio','Global Hair Colour',120,3200,5],['SVC-DEMO-04','Skin & Facial','Hydra Glow Facial',75,1850,5],['SVC-DEMO-05','Skin & Facial','Fruit Cleanup',45,850,5],['SVC-DEMO-06','Nails','Classic Manicure',45,750,5],['SVC-DEMO-07','Nails','Gel Pedicure',60,1200,5],['SVC-DEMO-08','Makeup','Party Makeup',75,3500,5],['SVC-DEMO-09','Makeup','Bridal Makeup Consultation',45,1000,5],['SVC-DEMO-10','Wellness','Head Massage',30,500,5],['SVC-DEMO-11','Skin & Facial','Eyebrow Threading',15,120,5],['SVC-DEMO-12','Skin & Facial','Full Arms Waxing',40,650,5],
    ];
    foreach ($services as [$code,$category,$name,$duration,$price,$tax]) insert($pdo, 'INSERT INTO services(service_code,category_id,name,duration_minutes,price,tax_rate,commission_type,commission_value,description) VALUES(?,?,?,?,?,?,\'percentage\',10,?)', [$code,id($pdo,'service_categories','name',$category),$name,$duration,$price,$tax,"Professional {$name} service"]);
    $serviceIds = $pdo->query("SELECT id FROM services WHERE service_code LIKE 'SVC-DEMO-%' ORDER BY service_code")->fetchAll(PDO::FETCH_COLUMN);

    $products = [
        ['PRD-DEMO-01','Hair Care','Velora Nourish Shampoo','VLR-SHP-250',325,480,4,8,'Glow Essentials'],['PRD-DEMO-02','Hair Care','Velora Silk Conditioner','VLR-CON-250',340,520,4,8,'Glow Essentials'],['PRD-DEMO-03','Hair Care','Argan Hair Serum','VLR-SER-50',420,690,5,5,'Luxe Beauty Supply'],['PRD-DEMO-04','Hair Care','Repair Hair Mask','VLR-MSK-200',480,760,5,4,'Luxe Beauty Supply'],['PRD-DEMO-05','Colour & Styling','Professional Hair Colour','VLR-COL-100',250,410,5,12,'Salon Mart India'],['PRD-DEMO-06','Skin Care','Gentle Face Wash','VLR-FW-100',190,320,5,7,'Glow Essentials'],['PRD-DEMO-07','Skin Care','Radiance Facial Cream','VLR-FC-50',380,650,5,5,'Luxe Beauty Supply'],['PRD-DEMO-08','Skin Care','Daily Defence Sunscreen','VLR-SS-50',310,540,5,3,'Luxe Beauty Supply'],['PRD-DEMO-09','Skin Care','Aloe Vera Wax','VLR-WAX-500',280,450,5,2,'Salon Mart India'],['PRD-DEMO-10','Nail Care','Cuticle Oil','VLR-CO-15',150,290,5,3,'Salon Mart India'],
    ];
    foreach ($products as [$code,$category,$name,$sku,$cost,$sell,$tax,$minimum,$supplier]) insert($pdo, 'INSERT INTO products(product_code,category_id,supplier_id,name,sku,barcode,brand,selling_price,purchase_price,tax_rate,current_stock,minimum_stock,unit) VALUES(?,?,?,?,?,?,\'Velora Pro\',?,?,?,?,?,\'pcs\')', [$code,id($pdo,'product_categories','name',$category),$supplierIds[array_search($supplier,array_column($suppliers,3),true)],$name,$sku,'890'.substr(md5($sku),0,10),$sell,$cost,$tax,0,$minimum]);
    $productIds = $pdo->query("SELECT id FROM products WHERE product_code LIKE 'PRD-DEMO-%' ORDER BY product_code")->fetchAll(PDO::FETCH_COLUMN);

    $customers = [['Aarohi Gupta','9810010001','female'],['Ishita Singh','9810010002','female'],['Nandini Rao','9810010003','female'],['Sana Khan','9810010004','female'],['Pooja Bansal','9810010005','female'],['Kritika Jain','9810010006','female'],['Anjali Menon','9810010007','female'],['Simran Kaur','9810010008','female'],['Tanvi Desai','9810010009','female'],['Rhea Chatterjee','9810010010','female'],['Sneha Kulkarni','9810010011','female'],['Divya Patel','9810010012','female'],['Mitali Sinha','9810010013','female'],['Shreya Bose','9810010014','female'],['Aditi Joshi','9810010015','female'],['Manisha Reddy','9810010016','female'],['Nisha Agarwal','9810010017','female'],['Bhavna Shah','9810010018','female']];
    foreach ($customers as $i => [$name,$mobile,$gender]) insert($pdo, 'INSERT INTO customers(customer_code,name,mobile,email,gender,dob,address,notes) VALUES(?,?,?,?,?,?,?,?)', [sprintf('CUS-DEMO-%02d',$i+1),$name,$mobile,'customer'.($i+1).'@veloraparlour.demo',$gender,'199'.($i%8).'-'.sprintf('%02d',($i%12)+1).'-'.sprintf('%02d',($i%25)+1),'Kolkata, West Bengal','Velora demo customer']);
    $customerIds = $pdo->query("SELECT id FROM customers WHERE customer_code LIKE 'CUS-DEMO-%' ORDER BY customer_code")->fetchAll(PDO::FETCH_COLUMN);
    $pdo->commit();

    // Purchases use the production operation's ledger rules, expressed directly here for historical dates.
    $pdo->beginTransaction();
    foreach ($productIds as $i => $productId) {
        $qty = 8 + ($i % 5) * 4; $product = $pdo->prepare('SELECT purchase_price,tax_rate,current_stock FROM products WHERE id=? FOR UPDATE'); $product->execute([$productId]); $product = $product->fetch();
        $date = date('Y-m-d', strtotime('-'.(28-$i).' days')); $net = $qty*(float)$product['purchase_price']; $tax = round($net*(float)$product['tax_rate']/100,2);
        $purchaseId=insert($pdo,'INSERT INTO purchases(purchase_number,supplier_id,supplier_invoice_no,purchase_date,subtotal,tax_total,grand_total,payment_status,created_by) VALUES(?,?,?,?,?,?,?,?,?)',[sprintf('PUR-DEMO-%02d',$i+1),$supplierIds[$i%3],'DEMO-'.$i,$date,$net,$tax,$net+$tax,'paid',$admin]);
        insert($pdo,'INSERT INTO purchase_items(purchase_id,product_id,quantity,unit_price,tax_rate,line_total) VALUES(?,?,?,?,?,?)',[$purchaseId,$productId,$qty,$product['purchase_price'],$product['tax_rate'],$net+$tax]);
        $after=(float)$product['current_stock']+$qty; $pdo->prepare('UPDATE products SET current_stock=? WHERE id=?')->execute([$after,$productId]); insert($pdo,"INSERT INTO stock_movements(product_id,movement_type,quantity,before_quantity,after_quantity,reference_type,reference_id,reason,created_by,created_at) VALUES(?,'purchase',?,?,?,?,?,?,?,?)",[$productId,$qty,$product['current_stock'],$after,'purchase',$purchaseId,'Opening demo purchase',$admin,$date.' 10:00:00']);
    }
    $pdo->commit();

    // Seed bills through BillingService, preserving its calculations, stock deduction, payments and commissions.
    $billing = new BillingService($pdo); $cash = id($pdo,'payment_methods','code','cash'); $upi=id($pdo,'payment_methods','code','upi'); $card=id($pdo,'payment_methods','code','card');
    for ($i=0; $i<12; $i++) { $lines=[['type'=>'service','id'=>(int)$serviceIds[$i%count($serviceIds)],'quantity'=>1,'staff_id'=>(int)$staffIds[$i%count($staffIds)]]]; if($i%2===0)$lines[]=['type'=>'product','id'=>(int)$productIds[$i%7],'quantity'=>1]; if($i%5===0)$lines[]=['type'=>'product','id'=>(int)$productIds[($i+3)%7],'quantity'=>2]; $invoiceId=$billing->finalize((int)$customerIds[$i],$lines,[$cash,$upi,$card][$i%3],0,$admin,0); $q=$pdo->prepare('SELECT grand_total FROM invoices WHERE id=?');$q->execute([$invoiceId]);$total=(float)$q->fetchColumn();$date=date('Y-m-d H:i:s',strtotime('-'.(11-$i).' days '.(10+$i%7).':30'));$pdo->prepare('UPDATE invoices SET created_at=?,paid_total=?,balance_total=0 WHERE id=?')->execute([$date,$total,$invoiceId]);insert($pdo,"INSERT INTO payments(payment_code,invoice_id,payment_method_id,amount,received_by,paid_at,status) VALUES(?,?,?,?,?,?,'captured')",[sprintf('PAY-DEMO-%02d',$i+1),$invoiceId,[$cash,$upi,$card][$i%3],$total,$admin,$date]); }

    $pdo->beginTransaction();
    foreach (range(0,11) as $i) { $start = date('Y-m-d H:i:s', strtotime((($i<5)?'+':'-').abs($i-5).' days '.(10+$i%5).':00')); $serviceId=(int)$serviceIds[$i%count($serviceIds)];$svc=$pdo->prepare('SELECT price,duration_minutes FROM services WHERE id=?');$svc->execute([$serviceId]);$svc=$svc->fetch();$staffId=(int)$staffIds[$i%count($staffIds)];$end=date('Y-m-d H:i:s',strtotime($start)+(int)$svc['duration_minutes']*60);$status=['pending','confirmed','checked_in','in_progress','completed','cancelled','no_show'][$i%7];$appointment=insert($pdo,'INSERT INTO appointments(appointment_code,customer_id,staff_id,start_at,end_at,status,notes,created_by) VALUES(?,?,?,?,?,?,?,?)',[sprintf('APT-DEMO-%02d',$i+1),$customerIds[($i+3)%count($customerIds)],$staffId,$start,$end,$status,'Demo appointment',$admin]);insert($pdo,'INSERT INTO appointment_services(appointment_id,service_id,staff_id,price,duration_minutes) VALUES(?,?,?,?,?)',[$appointment,$serviceId,$staffId,$svc['price'],$svc['duration_minutes']]); }
    foreach ([['EXP-DEMO-01','Studio rent',28000,'Rent',20],['EXP-DEMO-02','Electricity bill',4200,'Utilities',12],['EXP-DEMO-03','Deep cleaning supplies',1650,'Cleaning',7],['EXP-DEMO-04','Instagram campaign',3200,'Marketing',3],['EXP-DEMO-05','Chair maintenance',1800,'Maintenance',1]] as [$code,$desc,$amount,$category,$days]) insert($pdo,'INSERT INTO expenses(expense_code,description,amount,expense_date,category_id,payment_method_id,reference_no,created_by) VALUES(?,?,?,?,?,?,?,?)',[$code,$desc,$amount,date('Y-m-d',strtotime("-$days days")),id($pdo,'expense_categories','name',$category),$cash,$code,$admin]);
    $pdo->prepare('INSERT INTO system_metadata(metadata_key,metadata_value) VALUES(?,?)')->execute([$marker,date('c')]);
    $pdo->commit();
    fwrite(STDOUT, "Demo data installed successfully. Sign in as admin@veloraparlour.demo.\n");
} catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); fwrite(STDERR, 'Seed failed: '.$e->getMessage()."\n"); exit(1); }
