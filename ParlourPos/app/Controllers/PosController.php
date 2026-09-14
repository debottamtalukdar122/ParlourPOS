<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Services\AuthService;use App\Services\BillingService;use App\Support\Flash;use App\Support\Request;use App\Support\Response;use App\Support\View;use PDO;
final class PosController extends Controller
{
    public function __construct(View $view, private readonly PDO $pdo)
    {
        parent::__construct($view);
    }

    public function index(Request $r): Response
    {
        if ($res = $this->permitted($this->pdo, 'pos.use')) {
            return $res;
        }

        return $this->render('pos.index', [
            'title' => 'Point of Sale',
            'customers' => $this->pdo->query('SELECT id, name, mobile FROM customers WHERE is_active=1 ORDER BY name')->fetchAll(),
            'staff' => $this->pdo->query('SELECT id, name FROM staff WHERE is_active=1 ORDER BY name')->fetchAll(),
            'services' => $this->pdo->query('SELECT id, name, price, tax_rate FROM services WHERE is_active=1 ORDER BY name')->fetchAll(),
            'products' => $this->pdo->query('SELECT id, name, selling_price, tax_rate, current_stock FROM products WHERE is_active=1 AND current_stock>0 ORDER BY name')->fetchAll(),
            'methods' => $this->pdo->query('SELECT id, name FROM payment_methods WHERE is_active=1 ORDER BY id')->fetchAll(),
            'csrf' => Flash::csrf(),
            'flash' => Flash::get()
        ]);
    }

    public function finalize(Request $r): Response
    {
        if ($res = $this->permitted($this->pdo, 'pos.use')) {
            return $res;
        }

        if (!Flash::validCsrf($r->input('_token'))) {
            Flash::set('danger', 'Invalid request.');
            return Response::redirect('/pos');
        }

        $raw = json_decode((string)$r->input('lines_json'), true);
        if (!is_array($raw)) {
            Flash::set('danger', 'Add POS items first.');
            return Response::redirect('/pos');
        }

        try {
            $invoice = (new BillingService($this->pdo))->finalize(
                (int)$r->input('customer_id'),
                $raw,
                (int)$r->input('payment_method_id'),
                (float)$r->input('paid_amount'),
                AuthService::userId(),
                (float)$r->input('discount_amount')
            );

            try {
                \App\Services\AuditService::log($this->pdo, 'finalized', 'invoices', $invoice, [
                    'customer_id' => (int)$r->input('customer_id'),
                    'paid_amount' => (float)$r->input('paid_amount')
                ]);
            } catch (\Throwable) {}

            Flash::set('success', 'Invoice finalized successfully.');
            return Response::redirect('/invoices/' . $invoice);
        } catch (\Throwable $e) {
            Flash::set('danger', $e->getMessage());
            return Response::redirect('/pos');
        }
    }

    public function invoice(Request $r, string $id): Response
    {
        if ($res = $this->permitted($this->pdo, 'pos.use')) {
            return $res;
        }

        $stmt = $this->pdo->prepare('SELECT i.*, c.name customer_name, c.mobile FROM invoices i JOIN customers c ON c.id=i.customer_id WHERE i.id=?');
        $stmt->execute([(int)$id]);
        $invoice = $stmt->fetch();

        if (!$invoice) {
            return Response::html('Invoice not found', 404);
        }

        $items = $this->pdo->prepare('SELECT * FROM invoice_items WHERE invoice_id=? ORDER BY line_no');
        $items->execute([(int)$id]);

        return $this->render('pos.invoice', [
            'title' => $invoice['invoice_number'],
            'invoice' => $invoice,
            'items' => $items->fetchAll(),
            'csrf' => Flash::csrf(),
            'flash' => Flash::get()
        ]);
    }
}
