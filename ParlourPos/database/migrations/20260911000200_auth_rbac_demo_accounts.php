<?php

declare(strict_types=1);

return ['up' => static function (PDO $pdo): void {
    // 1. Ensure required roles exist
    $roles = ['Super Admin', 'Manager', 'Receptionist / Cashier', 'Staff'];
    foreach ($roles as $role) {
        $pdo->prepare('INSERT IGNORE INTO roles(name) VALUES(?)')->execute([$role]);
    }

    // 2. Ensure all granular permissions exist
    $permissions = [
        'dashboard.view', 'customers.manage', 'appointments.manage', 'pos.use',
        'catalog.view', 'catalog.manage', 'staff.manage', 'inventory.manage',
        'purchases.manage', 'expenses.manage', 'reports.view', 'settings.manage',
        'users.manage', 'audit.view', 'commissions.view', 'refunds.manage'
    ];
    foreach ($permissions as $code) {
        $pdo->prepare('INSERT IGNORE INTO permissions(code) VALUES(?)')->execute([$code]);
    }

    // 3. Map role permissions
    $permMap = $pdo->query('SELECT code, id FROM permissions')->fetchAll(PDO::FETCH_KEY_PAIR);
    $roleMap = $pdo->query('SELECT name, id FROM roles')->fetchAll(PDO::FETCH_KEY_PAIR);

    $grants = [
        'Super Admin' => array_keys($permMap),
        'Manager' => [
            'dashboard.view', 'customers.manage', 'appointments.manage', 'pos.use',
            'catalog.view', 'catalog.manage', 'staff.manage', 'inventory.manage',
            'purchases.manage', 'expenses.manage', 'reports.view', 'commissions.view', 'refunds.manage'
        ],
        'Receptionist / Cashier' => [
            'dashboard.view', 'customers.manage', 'appointments.manage', 'pos.use', 'catalog.view'
        ],
        'Staff' => [
            'dashboard.view', 'appointments.manage', 'commissions.view'
        ]
    ];

    foreach ($grants as $role => $codes) {
        if (!isset($roleMap[$role])) continue;
        $rId = (int)$roleMap[$role];
        foreach ($codes as $code) {
            if (isset($permMap[$code])) {
                $pId = (int)$permMap[$code];
                $pdo->prepare('INSERT IGNORE INTO role_permissions(role_id, permission_id) VALUES(?,?)')->execute([$rId, $pId]);
            }
        }
    }

    // 4. Find staff id 1 for beautician/staff demo
    $staffId = (int)$pdo->query("SELECT id FROM staff WHERE is_active=1 ORDER BY id ASC LIMIT 1")->fetchColumn() ?: null;

    // 5. Seed/Update demo accounts for each required role
    $demoUsers = [
        [
            'name' => 'Ananya Mehta',
            'email' => 'admin@veloraparlour.demo',
            'password' => 'Admin@123',
            'role' => 'Super Admin',
            'staff_id' => null,
        ],
        [
            'name' => 'Kavita Sen',
            'email' => 'manager@veloraparlour.demo',
            'password' => 'Manager@123',
            'role' => 'Manager',
            'staff_id' => null,
        ],
        [
            'name' => 'Rohit Verma',
            'email' => 'receptionist@veloraparlour.demo',
            'password' => 'Cashier@123',
            'role' => 'Receptionist / Cashier',
            'staff_id' => null,
        ],
        [
            'name' => 'Priya Sharma',
            'email' => 'staff@veloraparlour.demo',
            'password' => 'Staff@123',
            'role' => 'Staff',
            'staff_id' => $staffId,
        ],
    ];

    foreach ($demoUsers as $u) {
        if (!isset($roleMap[$u['role']])) continue;
        $roleId = (int)$roleMap[$u['role']];
        $hash = password_hash($u['password'], PASSWORD_DEFAULT);

        $existing = $pdo->prepare('SELECT id FROM users WHERE email=?');
        $existing->execute([$u['email']]);
        $existingId = $existing->fetchColumn();

        if ($existingId) {
            $pdo->prepare('UPDATE users SET name=?, password_hash=?, role_id=?, staff_id=?, is_active=1 WHERE id=?')
                ->execute([$u['name'], $hash, $roleId, $u['staff_id'], $existingId]);
        } else {
            $pdo->prepare('INSERT INTO users(name, email, password_hash, role_id, staff_id, is_active) VALUES(?,?,?,?,?,1)')
                ->execute([$u['name'], $u['email'], $hash, $roleId, $u['staff_id']]);
        }
    }
}];
