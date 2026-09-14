<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Services\AuthService; use App\Support\Flash; use App\Support\Request; use App\Support\Response; use App\Support\View; use PDO;
final class AppController extends Controller {
    /** @var array<string,array{table:string,title:string,fields:array<string,string>}> */ private array $modules;
    public function __construct(View $view, private readonly PDO $pdo) { parent::__construct($view); $this->modules=[
      'customers'=>['table'=>'customers','title'=>'Customers','fields'=>['name'=>'Name','mobile'=>'Mobile','email'=>'Email','gender'=>'Gender','notes'=>'Notes']],
      'services'=>['table'=>'services','title'=>'Services','fields'=>['name'=>'Name','duration_minutes'=>'Duration (minutes)','price'=>'Price','tax_rate'=>'Tax %','commission_value'=>'Commission value']],
      'products'=>['table'=>'products','title'=>'Products','fields'=>['name'=>'Name','sku'=>'SKU','selling_price'=>'Selling price','purchase_price'=>'Purchase price','tax_rate'=>'Tax %','current_stock'=>'Opening stock','minimum_stock'=>'Minimum stock','unit'=>'Unit']],
      'suppliers'=>['table'=>'suppliers','title'=>'Suppliers','fields'=>['name'=>'Name','company'=>'Company','mobile'=>'Mobile']],
      'expenses'=>['table'=>'expenses','title'=>'Expenses','fields'=>['description'=>'Description','amount'=>'Amount','expense_date'=>'Expense date']],
    ]; }
    private function code(string $prefix): string { return $prefix.'-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(2))); }

    public function dashboard(Request $r): Response
    {
        if ($x = $this->permitted($this->pdo, 'dashboard.view')) {
            return $x;
        }

        $staffId = AuthService::userStaffId();
        $isStaff = AuthService::isStaff() || ($staffId !== null && !AuthService::can($this->pdo, 'reports.view'));

        if ($isStaff && $staffId) {
            $staffProfile = $this->pdo->prepare('SELECT * FROM staff WHERE id=?');
            $staffProfile->execute([$staffId]);
            $staff = $staffProfile->fetch() ?: [];

            $todayAptsStmt = $this->pdo->prepare("SELECT COUNT(*) FROM appointments WHERE staff_id=? AND DATE(start_at)=CURDATE() AND status NOT IN ('cancelled','no_show')");
            $todayAptsStmt->execute([$staffId]);
            $todayApts = (int)$todayAptsStmt->fetchColumn();

            $totalAptsStmt = $this->pdo->prepare("SELECT COUNT(*) FROM appointments WHERE staff_id=?");
            $totalAptsStmt->execute([$staffId]);
            $totalApts = (int)$totalAptsStmt->fetchColumn();

            $completedStmt = $this->pdo->prepare("SELECT COUNT(*) FROM appointments WHERE staff_id=? AND status='completed'");
            $completedStmt->execute([$staffId]);
            $completedApts = (int)$completedStmt->fetchColumn();

            $commissionsStmt = $this->pdo->prepare("SELECT COALESCE(SUM(commission_amount),0) FROM commissions WHERE staff_id=?");
            $commissionsStmt->execute([$staffId]);
            $totalCommissions = (float)$commissionsStmt->fetchColumn();

            $upcomingStmt = $this->pdo->prepare("SELECT a.id, a.appointment_code, a.start_at, a.end_at, a.status, c.name customer_name, c.mobile customer_mobile 
                FROM appointments a 
                JOIN customers c ON c.id=a.customer_id 
                WHERE a.staff_id=? AND a.status NOT IN ('cancelled','no_show') 
                ORDER BY a.start_at ASC LIMIT 10");
            $upcomingStmt->execute([$staffId]);
            $upcomingAppointments = $upcomingStmt->fetchAll();

            $data = [
                'isStaff' => true,
                'staff' => $staff,
                'todayAppointments' => $todayApts,
                'totalAppointments' => $totalApts,
                'completedAppointments' => $completedApts,
                'totalCommissions' => $totalCommissions,
                'upcomingAppointments' => $upcomingAppointments,
            ];

            return $this->render('dashboard.index', [
                'title' => 'Staff Dashboard',
                'data' => $data,
                'csrf' => Flash::csrf(),
                'flash' => Flash::get(),
            ]);
        }

        $data = [
            'isStaff' => false,
            'customers' => (int)$this->pdo->query('SELECT COUNT(*) FROM customers WHERE is_active=1')->fetchColumn(),
            'appointments' => (int)$this->pdo->query("SELECT COUNT(*) FROM appointments WHERE DATE(start_at)=CURDATE() AND status NOT IN ('cancelled','no_show')")->fetchColumn(),
            'sales' => (float)$this->pdo->query("SELECT COALESCE(SUM(grand_total),0) FROM invoices WHERE DATE(created_at)=CURDATE() AND status='finalized'")->fetchColumn(),
            'bills' => (int)$this->pdo->query("SELECT COUNT(*) FROM invoices WHERE DATE(created_at)=CURDATE() AND status='finalized'")->fetchColumn(),
            'expenses' => (float)$this->pdo->query('SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date=CURDATE()')->fetchColumn(),
            'pending' => (int)$this->pdo->query("SELECT COUNT(*) FROM appointments WHERE status IN ('pending','confirmed')")->fetchColumn(),
            'lowStock' => $this->pdo->query('SELECT name,current_stock,minimum_stock FROM products WHERE current_stock <= minimum_stock AND is_active=1 ORDER BY current_stock ASC LIMIT 8')->fetchAll(),
            'recentInvoices' => $this->pdo->query("SELECT i.id,i.invoice_number,i.grand_total,i.created_at,c.name customer_name FROM invoices i JOIN customers c ON c.id=i.customer_id WHERE i.status='finalized' ORDER BY i.created_at DESC LIMIT 6")->fetchAll(),
            'upcomingAppointments' => $this->pdo->query("SELECT a.start_at,a.status,c.name customer_name,s.name staff_name FROM appointments a JOIN customers c ON c.id=a.customer_id LEFT JOIN staff s ON s.id=a.staff_id WHERE a.status NOT IN ('cancelled','no_show','completed') ORDER BY a.start_at ASC LIMIT 6")->fetchAll()
        ];

        return $this->render('dashboard.index', [
            'title' => 'Dashboard',
            'data' => $data,
            'csrf' => Flash::csrf(),
            'flash' => Flash::get(),
        ]);
    }

    public function index(Request $r, string $module): Response
    {
        $readPermissions = [
            'customers' => 'customers.manage',
            'staff' => 'staff.manage',
            'services' => 'catalog.view',
            'products' => 'catalog.view',
            'suppliers' => 'purchases.manage',
            'expenses' => 'expenses.manage',
        ];

        $perm = $readPermissions[$module] ?? null;
        if (!$perm || ($x = $this->permitted($this->pdo, $perm))) {
            return $x ?? Response::html('Not found', 404);
        }

        $m = $this->modules[$module] ?? null;
        if (!$m) return Response::html('Not found', 404);

        $rows = $this->pdo->query("SELECT * FROM {$m['table']} ORDER BY id DESC LIMIT 100")->fetchAll();
        return $this->render('modules.index', [
            'title' => $m['title'],
            'module' => $module,
            'meta' => $m,
            'rows' => $rows,
            'csrf' => Flash::csrf(),
            'flash' => Flash::get(),
        ]);
    }

    public function create(Request $r, string $module): Response
    {
        $writePermissions = [
            'customers' => 'customers.manage',
            'staff' => 'staff.manage',
            'services' => 'catalog.manage',
            'products' => 'catalog.manage',
            'suppliers' => 'purchases.manage',
            'expenses' => 'expenses.manage',
        ];

        $perm = $writePermissions[$module] ?? null;
        if (!$perm || ($x = $this->permitted($this->pdo, $perm))) {
            return $x ?? Response::html('Not found', 404);
        }

        $m = $this->modules[$module] ?? null;
        if (!$m) return Response::html('Not found', 404);

        if (!Flash::validCsrf($r->input('_token'))) {
            Flash::set('danger', 'Invalid request.');
            return Response::redirect('/' . $module);
        }

        $input = [];
        foreach ($m['fields'] as $field => $_) {
            $input[$field] = trim((string)$r->input($field));
        }

        if (($input['name'] ?? $input['description'] ?? '') === '') {
            Flash::set('danger', 'Complete the required field.');
            return Response::redirect('/' . $module);
        }

        $table = $m['table'];
        foreach (['commission_value','duration_minutes','price','tax_rate','selling_price','purchase_price','current_stock','minimum_stock','amount'] as $number) {
            if (array_key_exists($number, $input) && $input[$number] === '') {
                $input[$number] = $number === 'duration_minutes' ? 30 : 0;
            }
        }

        if (($input['unit'] ?? null) === '') $input['unit'] = 'pcs';
        if (isset($input['current_stock'])) $input['current_stock'] = (float)$input['current_stock'];

        if ($table === 'customers') $input = ['customer_code' => $this->code('CUS')] + $input;
        if ($table === 'staff') $input = ['staff_code' => $this->code('STF'), 'commission_type' => 'percentage'] + $input;
        if ($table === 'services') $input = ['service_code' => $this->code('SVC'), 'commission_type' => 'percentage'] + $input;
        if ($table === 'products') $input = ['product_code' => $this->code('PRD')] + $input;
        if ($table === 'suppliers') $input = ['supplier_code' => $this->code('SUP')] + $input;
        if ($table === 'expenses') $input = ['expense_code' => $this->code('EXP'), 'created_by' => AuthService::userId()] + $input;

        $columns = array_keys($input);
        $this->pdo->prepare('INSERT INTO ' . $table . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', array_fill(0, count($columns), '?')) . ')')->execute(array_values($input));
        $id = (int)$this->pdo->lastInsertId();

        if ($table === 'products' && (float)$input['current_stock'] !== 0.0) {
            $this->pdo->prepare("INSERT INTO stock_movements(product_id,movement_type,quantity,before_quantity,after_quantity,reason,created_by) VALUES(?,'adjustment',?,0,?,'Opening balance',?)")->execute([$id, $input['current_stock'], $input['current_stock'], AuthService::userId()]);
        }

        try {
            \App\Services\AuditService::log($this->pdo, 'created', $module, $id, $input);
        } catch (\Throwable) {}

        Flash::set('success', $m['title'] . ' record saved.');
        return Response::redirect('/' . $module);
    }
}
