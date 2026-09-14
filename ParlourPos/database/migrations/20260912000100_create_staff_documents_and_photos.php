<?php

declare(strict_types=1);

return ['up' => static function (PDO $pdo): void {
    // 1. Create staff_documents table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS staff_documents (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            staff_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(150) NOT NULL,
            document_type VARCHAR(50) NOT NULL DEFAULT 'other',
            file_path VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_size INT UNSIGNED NOT NULL DEFAULT 0,
            mime_type VARCHAR(100) NOT NULL DEFAULT 'application/octet-stream',
            created_by BIGINT UNSIGNED NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_staff_docs_staff_id (staff_id),
            CONSTRAINT fk_staff_docs_staff FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE,
            CONSTRAINT fk_staff_docs_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. Assign royalty-free local portraits to existing staff members
    $photoMap = [
        1 => '/uploads/staff/photos/staff_priya_sharma.jpg',
        2 => '/uploads/staff/photos/staff_neha_kapoor.jpg',
        3 => '/uploads/staff/photos/staff_riya_verma.jpg',
        4 => '/uploads/staff/photos/staff_kavya_iyer.jpg',
        5 => '/uploads/staff/photos/staff_meera_nair.jpg',
        6 => '/uploads/staff/photos/staff_debarpan_dutta.jpg',
    ];

    $updateStmt = $pdo->prepare("UPDATE staff SET profile_photo_path = ? WHERE id = ? AND (profile_photo_path IS NULL OR profile_photo_path = '')");
    foreach ($photoMap as $staffId => $photoPath) {
        $updateStmt->execute([$photoPath, $staffId]);
    }
}];
