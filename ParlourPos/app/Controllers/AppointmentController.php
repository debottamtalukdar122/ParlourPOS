<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Services\AuthService;use App\Support\Flash;use App\Support\Request;use App\Support\Response;use App\Support\View;use PDO;
final class AppointmentController extends Controller
{
    public function __construct(View $view, private readonly PDO $pdo)
    {
        parent::__construct($view);
    }

    public function index(Request $r): Response
    {
        if ($res = $this->permitted($this->pdo, 'appointments.manage')) {
            return $res;
        }

        $staffId = AuthService::userStaffId();
        $isStaff = AuthService::isStaff() || ($staffId !== null && !AuthService::can($this->pdo, 'reports.view'));

        if ($isStaff && $staffId) {
            $stmt = $this->pdo->prepare('SELECT a.*, c.name customer_name, c.mobile customer_mobile, s.name staff_name 
                                         FROM appointments a 
                                         JOIN customers c ON c.id=a.customer_id 
                                         LEFT JOIN staff s ON s.id=a.staff_id 
                                         WHERE a.staff_id = ? 
                                         ORDER BY a.start_at DESC LIMIT 100');
            $stmt->execute([$staffId]);
            $rows = $stmt->fetchAll();
        } else {
            $rows = $this->pdo->query('SELECT a.*, c.name customer_name, c.mobile customer_mobile, s.name staff_name 
                                       FROM appointments a 
                                       JOIN customers c ON c.id=a.customer_id 
                                       LEFT JOIN staff s ON s.id=a.staff_id 
                                       ORDER BY a.start_at DESC LIMIT 100')->fetchAll();
        }

        return $this->render('appointments.index', [
            'title' => 'Appointments',
            'rows' => $rows,
            'isStaff' => $isStaff,
            'customers' => $this->pdo->query('SELECT id, name FROM customers WHERE is_active=1 ORDER BY name')->fetchAll(),
            'staff' => $this->pdo->query('SELECT id, name FROM staff WHERE is_active=1 ORDER BY name')->fetchAll(),
            'services' => $this->pdo->query('SELECT id, name, price, duration_minutes FROM services WHERE is_active=1 ORDER BY name')->fetchAll(),
            'csrf' => Flash::csrf(),
            'flash' => Flash::get()
        ]);
    }

    public function create(Request $r): Response
    {
        if ($res = $this->permitted($this->pdo, 'appointments.manage')) {
            return $res;
        }

        if (!Flash::validCsrf($r->input('_token'))) {
            Flash::set('danger', 'Invalid request.');
            return Response::redirect('/appointments');
        }

        $customer = (int)$r->input('customer_id');
        $staff = (int)$r->input('staff_id');
        $service = (int)$r->input('service_id');
        $start = str_replace('T', ' ', (string)$r->input('start_at'));

        $svc = $this->pdo->prepare('SELECT * FROM services WHERE id=? AND is_active=1');
        $svc->execute([$service]);
        $svc = $svc->fetch();

        if (!$customer || !$staff || !$svc || strtotime($start) === false) {
            Flash::set('danger', 'Complete all appointment details.');
            return Response::redirect('/appointments');
        }

        $end = date('Y-m-d H:i:s', strtotime($start) + ((int)$svc['duration_minutes'] * 60));
        $overlap = $this->pdo->prepare("SELECT COUNT(*) FROM appointments WHERE staff_id=? AND status NOT IN ('cancelled','no_show') AND start_at < ? AND end_at > ?");
        $overlap->execute([$staff, $end, $start]);

        if ((int)$overlap->fetchColumn()) {
            Flash::set('danger', 'This staff member is already booked in that time range.');
            return Response::redirect('/appointments');
        }

        $code = 'APT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
        $notes = trim((string)$r->input('notes'));

        $this->pdo->prepare('INSERT INTO appointments(appointment_code,customer_id,staff_id,start_at,end_at,notes,created_by) VALUES(?,?,?,?,?,?,?)')
            ->execute([$code, $customer, $staff, $start, $end, $notes, AuthService::userId()]);
        $id = (int)$this->pdo->lastInsertId();

        $this->pdo->prepare('INSERT INTO appointment_services(appointment_id,service_id,staff_id,price,duration_minutes) VALUES(?,?,?,?,?)')
            ->execute([$id, $service, $staff, $svc['price'], $svc['duration_minutes']]);

        try {
            \App\Services\AuditService::log($this->pdo, 'created', 'appointments', $id, [
                'code' => $code,
                'customer_id' => $customer,
                'staff_id' => $staff,
                'start_at' => $start
            ]);
        } catch (\Throwable) {}

        Flash::set('success', 'Appointment booked successfully.');
        return Response::redirect('/appointments');
    }

    public function updateStatus(Request $r): Response
    {
        if ($res = $this->permitted($this->pdo, 'appointments.manage')) {
            return $res;
        }

        if (!Flash::validCsrf($r->input('_token'))) {
            Flash::set('danger', 'Invalid request.');
            return Response::redirect('/appointments');
        }

        $id = (int)$r->input('id');
        $newStatus = trim((string)$r->input('status'));

        $allowedStatuses = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'];
        if (!in_array($newStatus, $allowedStatuses, true)) {
            Flash::set('danger', 'Invalid appointment status.');
            return Response::redirect('/appointments');
        }

        $stmt = $this->pdo->prepare('SELECT * FROM appointments WHERE id=?');
        $stmt->execute([$id]);
        $appointment = $stmt->fetch();

        if (!$appointment) {
            Flash::set('danger', 'Appointment not found.');
            return Response::redirect('/appointments');
        }

        $staffId = AuthService::userStaffId();
        $isStaff = AuthService::isStaff() || ($staffId !== null && !AuthService::can($this->pdo, 'reports.view'));

        // If staff user, restrict status updates to appointments assigned to them
        if ($isStaff && $staffId && (int)$appointment['staff_id'] !== $staffId) {
            Flash::set('danger', 'You are only authorized to update your own assigned appointments.');
            return Response::redirect('/appointments');
        }

        $this->pdo->prepare('UPDATE appointments SET status=? WHERE id=?')->execute([$newStatus, $id]);

        try {
            \App\Services\AuditService::log($this->pdo, 'status_updated', 'appointments', $id, [
                'old_status' => $appointment['status'],
                'new_status' => $newStatus,
            ]);
        } catch (\Throwable) {}

        Flash::set('success', "Appointment status updated to '" . ucwords(str_replace('_', ' ', $newStatus)) . "'.");
        return Response::redirect('/appointments');
    }
}
