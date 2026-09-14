<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuditService;
use App\Services\AuthService;
use App\Support\Flash;
use App\Support\Request;
use App\Support\Response;
use App\Support\View;
use PDO;

final class StaffController extends Controller
{
    public function __construct(View $view, private readonly PDO $pdo)
    {
        parent::__construct($view);
    }

    private function code(): string
    {
        return 'STF-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
    }

    public function index(Request $r): Response
    {
        if ($res = $this->permitted($this->pdo, 'staff.manage')) {
            return $res;
        }

        $search = trim((string)$r->query('search', ''));
        $statusFilter = (string)$r->query('status', '');

        $sql = "SELECT s.*,
                       u.id as user_id, u.name as user_name, u.email as user_email, u.is_active as user_active,
                       (SELECT COUNT(*) FROM staff_documents d WHERE d.staff_id = s.id) as documents_count,
                       (SELECT COUNT(*) FROM appointments a WHERE a.staff_id = s.id) as appointments_count,
                       (SELECT COALESCE(SUM(c.commission_amount), 0) FROM commissions c WHERE c.staff_id = s.id) as total_commissions
                FROM staff s
                LEFT JOIN users u ON u.staff_id = s.id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (s.name LIKE ? OR s.staff_code LIKE ? OR s.designation LIKE ? OR s.mobile LIKE ? OR s.email LIKE ?)";
            $term = '%' . $search . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if ($statusFilter === 'active') {
            $sql .= " AND s.is_active = 1";
        } elseif ($statusFilter === 'inactive') {
            $sql .= " AND s.is_active = 0";
        }

        $sql .= " ORDER BY s.is_active DESC, s.id ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all documents for modals
        $docsStmt = $this->pdo->query("
            SELECT d.*, u.name as uploaded_by_name
            FROM staff_documents d
            LEFT JOIN users u ON u.id = d.created_by
            ORDER BY d.id DESC
        ");
        $allDocs = $docsStmt->fetchAll(PDO::FETCH_ASSOC);

        $documentsByStaff = [];
        foreach ($allDocs as $doc) {
            $documentsByStaff[(int)$doc['staff_id']][] = $doc;
        }

        return $this->render('staff.index', [
            'title' => 'Staff Directory',
            'staffList' => $staffList,
            'documentsByStaff' => $documentsByStaff,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'csrf' => Flash::csrf(),
            'flash' => Flash::get(),
        ]);
    }

    public function create(Request $r): Response
    {
        if ($res = $this->permitted($this->pdo, 'staff.manage')) {
            return $res;
        }

        if (!Flash::validCsrf($r->input('_token'))) {
            Flash::set('danger', 'Your session expired. Please try again.');
            return Response::redirect('/staff');
        }

        $name = trim((string)$r->input('name'));
        $designation = trim((string)$r->input('designation')) ?: 'Stylist';
        $mobile = trim((string)$r->input('mobile')) ?: null;
        $email = trim((string)$r->input('email')) ?: null;
        $gender = trim((string)$r->input('gender')) ?: null;
        $dob = trim((string)$r->input('dob')) ?: null;
        $joiningDate = trim((string)$r->input('joining_date')) ?: date('Y-m-d');
        $address = trim((string)$r->input('address')) ?: null;
        $commissionType = $r->input('commission_type') === 'fixed' ? 'fixed' : 'percentage';
        $commissionValue = max(0.0, (float)$r->input('commission_value', 10.0));
        $salary = $r->input('salary') !== '' && $r->input('salary') !== null ? (float)$r->input('salary') : null;
        $isActive = (int)$r->input('is_active', 1) === 1 ? 1 : 0;

        if ($name === '') {
            Flash::set('danger', 'Staff member name is required.');
            return Response::redirect('/staff');
        }

        // Profile Photo Upload handling
        $profilePhotoPath = null;
        if (!empty($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $photo = $_FILES['profile_photo'];
            $maxPhotoSize = 5 * 1024 * 1024; // 5MB

            if ($photo['size'] > $maxPhotoSize) {
                Flash::set('danger', 'Profile photo must be 5MB or less.');
                return Response::redirect('/staff');
            }

            $ext = strtolower(pathinfo((string)$photo['name'], PATHINFO_EXTENSION));
            if (!$this->isValidImage($photo['tmp_name'], $ext)) {
                Flash::set('danger', 'Profile photo must be a valid JPG, PNG, or WEBP image.');
                return Response::redirect('/staff');
            }

            $targetDir = BASE_PATH . '/public/uploads/staff/photos';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $safeFilename = 'staff_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $targetPath = $targetDir . '/' . $safeFilename;

            if (move_uploaded_file($photo['tmp_name'], $targetPath)) {
                $profilePhotoPath = '/uploads/staff/photos/' . $safeFilename;
            }
        }

        $staffCode = $this->code();

        $stmt = $this->pdo->prepare("
            INSERT INTO staff (
                staff_code, name, mobile, email, designation,
                commission_type, commission_value, salary, gender, dob,
                joining_date, address, profile_photo_path, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $staffCode, $name, $mobile, $email, $designation,
            $commissionType, $commissionValue, $salary, $gender, $dob,
            $joiningDate, $address, $profilePhotoPath, $isActive
        ]);

        $staffId = (int)$this->pdo->lastInsertId();

        // Optional initial document upload
        if (!empty($_FILES['initial_document']) && $_FILES['initial_document']['error'] === UPLOAD_ERR_OK) {
            $this->saveUploadedDocument(
                $staffId,
                $_FILES['initial_document'],
                trim((string)$r->input('initial_doc_title')) ?: 'Initial Document',
                trim((string)$r->input('initial_doc_type')) ?: 'other'
            );
        }

        try {
            AuditService::log($this->pdo, 'created', 'staff', $staffId, [
                'name' => $name,
                'staff_code' => $staffCode,
                'designation' => $designation
            ]);
        } catch (\Throwable) {}

        Flash::set('success', "Staff member '{$name}' added successfully.");
        return Response::redirect('/staff');
    }

    public function update(Request $r): Response
    {
        if ($res = $this->permitted($this->pdo, 'staff.manage')) {
            return $res;
        }

        if (!Flash::validCsrf($r->input('_token'))) {
            Flash::set('danger', 'Your session expired. Please try again.');
            return Response::redirect('/staff');
        }

        $id = (int)$r->input('id');
        $stmt = $this->pdo->prepare("SELECT * FROM staff WHERE id = ?");
        $stmt->execute([$id]);
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$staff) {
            Flash::set('danger', 'Staff member not found.');
            return Response::redirect('/staff');
        }

        $name = trim((string)$r->input('name'));
        $designation = trim((string)$r->input('designation')) ?: 'Stylist';
        $mobile = trim((string)$r->input('mobile')) ?: null;
        $email = trim((string)$r->input('email')) ?: null;
        $gender = trim((string)$r->input('gender')) ?: null;
        $dob = trim((string)$r->input('dob')) ?: null;
        $joiningDate = trim((string)$r->input('joining_date')) ?: null;
        $address = trim((string)$r->input('address')) ?: null;
        $commissionType = $r->input('commission_type') === 'fixed' ? 'fixed' : 'percentage';
        $commissionValue = max(0.0, (float)$r->input('commission_value', 0.0));
        $salary = $r->input('salary') !== '' && $r->input('salary') !== null ? (float)$r->input('salary') : null;
        $isActive = (int)$r->input('is_active', 1) === 1 ? 1 : 0;

        if ($name === '') {
            Flash::set('danger', 'Staff name cannot be empty.');
            return Response::redirect('/staff');
        }

        $profilePhotoPath = $staff['profile_photo_path'];

        // Handle Photo Replacement
        if (!empty($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $photo = $_FILES['profile_photo'];
            $maxPhotoSize = 5 * 1024 * 1024;

            if ($photo['size'] > $maxPhotoSize) {
                Flash::set('danger', 'Profile photo must be 5MB or less.');
                return Response::redirect('/staff');
            }

            $ext = strtolower(pathinfo((string)$photo['name'], PATHINFO_EXTENSION));
            if (!$this->isValidImage($photo['tmp_name'], $ext)) {
                Flash::set('danger', 'Profile photo must be a valid JPG, PNG, or WEBP image.');
                return Response::redirect('/staff');
            }

            $targetDir = BASE_PATH . '/public/uploads/staff/photos';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $safeFilename = 'staff_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $targetPath = $targetDir . '/' . $safeFilename;

            if (move_uploaded_file($photo['tmp_name'], $targetPath)) {
                // Safely remove previous non-demo custom photo
                if ($profilePhotoPath && str_starts_with($profilePhotoPath, '/uploads/staff/photos/staff_') && !str_contains($profilePhotoPath, 'priya') && !str_contains($profilePhotoPath, 'neha') && !str_contains($profilePhotoPath, 'riya') && !str_contains($profilePhotoPath, 'kavya') && !str_contains($profilePhotoPath, 'meera') && !str_contains($profilePhotoPath, 'debarpan')) {
                    $oldFullPath = BASE_PATH . '/public' . $profilePhotoPath;
                    if (file_exists($oldFullPath)) {
                        @unlink($oldFullPath);
                    }
                }
                $profilePhotoPath = '/uploads/staff/photos/' . $safeFilename;
            }
        }

        $updateStmt = $this->pdo->prepare("
            UPDATE staff SET
                name = ?, mobile = ?, email = ?, designation = ?,
                commission_type = ?, commission_value = ?, salary = ?, gender = ?,
                dob = ?, joining_date = ?, address = ?, profile_photo_path = ?, is_active = ?
            WHERE id = ?
        ");

        $updateStmt->execute([
            $name, $mobile, $email, $designation,
            $commissionType, $commissionValue, $salary, $gender,
            $dob, $joiningDate, $address, $profilePhotoPath, $isActive,
            $id
        ]);

        try {
            AuditService::log($this->pdo, 'updated', 'staff', $id, [
                'name' => $name,
                'designation' => $designation,
                'is_active' => $isActive
            ]);
        } catch (\Throwable) {}

        Flash::set('success', "Staff profile for '{$name}' updated successfully.");
        return Response::redirect('/staff');
    }

    public function toggleStatus(Request $r): Response
    {
        if ($res = $this->permitted($this->pdo, 'staff.manage')) {
            return $res;
        }

        if (!Flash::validCsrf($r->input('_token'))) {
            Flash::set('danger', 'Your session expired. Please try again.');
            return Response::redirect('/staff');
        }

        $id = (int)$r->input('id');
        $stmt = $this->pdo->prepare("SELECT id, name, is_active FROM staff WHERE id = ?");
        $stmt->execute([$id]);
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$staff) {
            Flash::set('danger', 'Staff member not found.');
            return Response::redirect('/staff');
        }

        $newStatus = ((int)$staff['is_active'] === 1) ? 0 : 1;
        $this->pdo->prepare("UPDATE staff SET is_active = ? WHERE id = ?")->execute([$newStatus, $id]);

        try {
            AuditService::log($this->pdo, 'toggled_status', 'staff', $id, [
                'name' => $staff['name'],
                'new_status' => $newStatus === 1 ? 'active' : 'inactive'
            ]);
        } catch (\Throwable) {}

        $label = $newStatus === 1 ? 'activated' : 'deactivated';
        Flash::set('success', "Staff member '{$staff['name']}' has been {$label}.");
        return Response::redirect('/staff');
    }

    public function uploadDocument(Request $r): Response
    {
        if ($res = $this->permitted($this->pdo, 'staff.manage')) {
            return $res;
        }

        if (!Flash::validCsrf($r->input('_token'))) {
            Flash::set('danger', 'Your session expired. Please try again.');
            return Response::redirect('/staff');
        }

        $staffId = (int)$r->input('staff_id');
        $title = trim((string)$r->input('title')) ?: 'Staff Document';
        $docType = trim((string)$r->input('document_type')) ?: 'other';

        if (empty($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            Flash::set('danger', 'Please select a document file to upload.');
            return Response::redirect('/staff');
        }

        $res = $this->saveUploadedDocument($staffId, $_FILES['document'], $title, $docType);
        if ($res['success']) {
            Flash::set('success', "Document '{$title}' uploaded successfully.");
        } else {
            Flash::set('danger', $res['error']);
        }

        return Response::redirect('/staff');
    }

    public function downloadDocument(Request $r, string $id): Response
    {
        $docId = (int)$id;
        $stmt = $this->pdo->prepare("SELECT * FROM staff_documents WHERE id = ?");
        $stmt->execute([$docId]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$doc) {
            return Response::html('<h1>404 - Document Not Found</h1>', 404);
        }

        // Authorization check: must have staff.manage OR be the staff member themselves
        $userStaffId = AuthService::userStaffId();
        $hasManage = AuthService::can($this->pdo, 'staff.manage');

        if (!$hasManage && ($userStaffId === null || (int)$userStaffId !== (int)$doc['staff_id'])) {
            return Response::html('<h1>403 - Forbidden: You do not have permission to access this document.</h1>', 403);
        }

        $baseDir = realpath(BASE_PATH . '/storage/uploads/staff/documents');
        $fullPath = realpath(BASE_PATH . '/' . $doc['file_path']);

        // Prevent path traversal
        if (!$fullPath || !$baseDir || !str_starts_with($fullPath, $baseDir) || !file_exists($fullPath)) {
            return Response::html('<h1>404 - File Not Found on Disk</h1>', 404);
        }

        $filename = basename((string)$doc['original_name']);
        $mime = $doc['mime_type'] ?: 'application/octet-stream';
        $size = filesize($fullPath);

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . addslashes($filename) . '"');
        header('Expires: 0');
        header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $size);

        readfile($fullPath);
        exit;
    }

    public function deleteDocument(Request $r): Response
    {
        if ($res = $this->permitted($this->pdo, 'staff.manage')) {
            return $res;
        }

        if (!Flash::validCsrf($r->input('_token'))) {
            Flash::set('danger', 'Your session expired. Please try again.');
            return Response::redirect('/staff');
        }

        $docId = (int)$r->input('document_id');
        $stmt = $this->pdo->prepare("SELECT * FROM staff_documents WHERE id = ?");
        $stmt->execute([$docId]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$doc) {
            Flash::set('danger', 'Document not found.');
            return Response::redirect('/staff');
        }

        $fullPath = BASE_PATH . '/' . $doc['file_path'];
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }

        $this->pdo->prepare("DELETE FROM staff_documents WHERE id = ?")->execute([$docId]);

        try {
            AuditService::log($this->pdo, 'deleted', 'staff_document', $docId, [
                'staff_id' => $doc['staff_id'],
                'title' => $doc['title']
            ]);
        } catch (\Throwable) {}

        Flash::set('success', "Document '{$doc['title']}' deleted.");
        return Response::redirect('/staff');
    }

    /**
     * Helper to validate and save staff document to protected storage
     * @return array{success:bool, error?:string, id?:int}
     */
    private function saveUploadedDocument(int $staffId, array $file, string $title, string $docType): array
    {
        $maxDocSize = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $maxDocSize) {
            return ['success' => false, 'error' => 'Document file must be 10MB or less.'];
        }

        $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        $mime = $this->getDocumentMime($file['tmp_name'], $ext);
        if ($mime === null) {
            return ['success' => false, 'error' => 'Allowed document formats: PDF, JPG, PNG, DOC, DOCX. Executable or unsafe files are rejected.'];
        }

        $targetDir = BASE_PATH . '/storage/uploads/staff/documents';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $safeFilename = 'doc_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $targetPath = $targetDir . '/' . $safeFilename;
        $relativeFilePath = 'storage/uploads/staff/documents/' . $safeFilename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => false, 'error' => 'Failed to save document file.'];
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO staff_documents (
                staff_id, title, document_type, file_path, original_name,
                file_size, mime_type, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $staffId,
            $title,
            $docType,
            $relativeFilePath,
            basename((string)$file['name']),
            (int)$file['size'],
            $mime,
            AuthService::userId()
        ]);

        $docId = (int)$this->pdo->lastInsertId();

        try {
            AuditService::log($this->pdo, 'uploaded', 'staff_document', $docId, [
                'staff_id' => $staffId,
                'title' => $title,
                'filename' => basename((string)$file['name'])
            ]);
        } catch (\Throwable) {}

        return ['success' => true, 'id' => $docId];
    }

    private function isValidImage(string $tmpPath, string $ext): bool
    {
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowedExts, true)) {
            return false;
        }

        if (function_exists('getimagesize')) {
            $info = @getimagesize($tmpPath);
            if (!$info || empty($info['mime'])) {
                return false;
            }
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            return in_array($info['mime'], $allowedMimes, true);
        }

        return true;
    }

    private function getDocumentMime(string $tmpPath, string $ext): ?string
    {
        $disallowedExts = ['php', 'phtml', 'exe', 'bat', 'sh', 'js', 'html', 'htm', 'vbs', 'cmd', 'cgi'];
        if (in_array($ext, $disallowedExts, true)) {
            return null;
        }

        $map = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        return $map[$ext] ?? null;
    }
}
