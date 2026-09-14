<?php

declare(strict_types=1);



return [
    'up' => static function (PDO $pdo): void {
        $pdo->exec(
            'CREATE TABLE system_metadata (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                metadata_key VARCHAR(100) NOT NULL,
                metadata_value VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_system_metadata_key (metadata_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    },
];
