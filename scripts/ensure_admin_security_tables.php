<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Support/helpers.php';
require dirname(__DIR__) . '/app/Support/Env.php';
require dirname(__DIR__) . '/app/Config/Database.php';

$connection = Database::connection(dirname(__DIR__));

$connection->exec(
    "CREATE TABLE IF NOT EXISTS password_reset_tokens (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        token_hash CHAR(64) NOT NULL UNIQUE,
        expires_at DATETIME NOT NULL,
        used_at DATETIME NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_password_reset_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_password_reset_tokens_user (user_id),
        INDEX idx_password_reset_tokens_lookup (token_hash, used_at, expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

echo "password_reset_tokens table is ready.\n";
