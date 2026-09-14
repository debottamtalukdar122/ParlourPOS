<?php

declare(strict_types=1);

namespace App\Support;

use PDO;
use RuntimeException;

final class Migrator
{
    public function __construct(private readonly PDO $pdo, private readonly string $migrationPath)
    {
    }

    public function migrate(): int
    {
        $this->ensureMigrationTable();
        $applied = $this->pdo->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
        $count = 0;

        foreach (glob($this->migrationPath . '/*.php') ?: [] as $file) {
            $migration = basename($file, '.php');
            if (in_array($migration, $applied, true)) {
                continue;
            }

            $definition = require $file;
            if (!is_array($definition) || !isset($definition['up']) || !is_callable($definition['up'])) {
                throw new RuntimeException("Migration [{$migration}] must return an array with an up callable.");
            }

           try {
    $definition['up']($this->pdo);

    $statement = $this->pdo->prepare(
        'INSERT INTO migrations (migration, applied_at)
         VALUES (:migration, UTC_TIMESTAMP())'
    );

    $statement->execute([
        'migration' => $migration
    ]);

    $count++;
    echo "Migrated: {$migration}" . PHP_EOL;

} catch (\Throwable $exception) {
    throw $exception;
}
        }

        return $count;
    }

    private function ensureMigrationTable(): void
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                applied_at TIMESTAMP NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }
}
