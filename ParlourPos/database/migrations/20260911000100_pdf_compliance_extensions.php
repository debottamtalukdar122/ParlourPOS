<?php
declare(strict_types=1);

return ['up' => static function (PDO $pdo): void {
    $statements = [
        "ALTER TABLE users ADD COLUMN staff_id BIGINT UNSIGNED NULL, ADD CONSTRAINT fk_users_staff FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE SET NULL",
        "ALTER TABLE staff ADD COLUMN email VARCHAR(190) NULL, ADD COLUMN gender VARCHAR(20) NULL, ADD COLUMN dob DATE NULL, ADD COLUMN address TEXT NULL, ADD COLUMN joining_date DATE NULL, ADD COLUMN salary DECIMAL(12,2) NULL, ADD COLUMN profile_photo_path VARCHAR(255) NULL",
        "ALTER TABLE suppliers ADD COLUMN email VARCHAR(190) NULL, ADD COLUMN address TEXT NULL, ADD COLUMN gstin VARCHAR(40) NULL, ADD COLUMN notes TEXT NULL",
        "ALTER TABLE audit_logs ADD COLUMN ip_address VARCHAR(45) NULL",
        "CREATE TABLE password_reset_tokens (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL, token_hash VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL, used_at DATETIME NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX(user_id,expires_at), FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB",
        "CREATE TABLE customer_notes (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, customer_id BIGINT UNSIGNED NOT NULL, note TEXT NOT NULL, created_by BIGINT UNSIGNED NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE, FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL) ENGINE=InnoDB",
    ];
    foreach ($statements as $sql) { try { $pdo->exec($sql); } catch (PDOException $e) { if (!str_contains($e->getMessage(), 'Duplicate column') && !str_contains($e->getMessage(), 'Duplicate key') && !str_contains($e->getMessage(), 'already exists')) throw $e; } }
    $pdo->exec("INSERT IGNORE INTO roles(name) VALUES ('Receptionist / Cashier')");
    foreach (['dashboard.view','customers.manage','appointments.manage','pos.use','catalog.view','catalog.manage','staff.manage','inventory.manage','purchases.manage','expenses.manage','reports.view','settings.manage','users.manage','audit.view','commissions.view','refunds.manage'] as $code) $pdo->prepare('INSERT IGNORE INTO permissions(code) VALUES(?)')->execute([$code]);
    $permissions = $pdo->query('SELECT code,id FROM permissions')->fetchAll(PDO::FETCH_KEY_PAIR); $roles=$pdo->query('SELECT name,id FROM roles')->fetchAll(PDO::FETCH_KEY_PAIR);
    $grants=['Super Admin'=>array_keys($permissions),'Manager'=>['dashboard.view','customers.manage','appointments.manage','pos.use','catalog.view','catalog.manage','staff.manage','inventory.manage','purchases.manage','expenses.manage','reports.view','commissions.view','refunds.manage'],'Receptionist / Cashier'=>['dashboard.view','customers.manage','appointments.manage','pos.use','catalog.view'],'Cashier'=>['dashboard.view','customers.manage','appointments.manage','pos.use','catalog.view'],'Staff'=>['dashboard.view','appointments.manage','commissions.view']];
    foreach ($grants as $role=>$codes) if (isset($roles[$role])) foreach ($codes as $code) if(isset($permissions[$code])) $pdo->prepare('INSERT IGNORE INTO role_permissions(role_id,permission_id) VALUES(?,?)')->execute([$roles[$role],$permissions[$code]]);
}];
