<?php

declare(strict_types=1);

require __DIR__ . '/../app/Support/Env.php';
require __DIR__ . '/../app/Config/Database.php';

$projectRoot = dirname(__DIR__);
$connection = Database::connection($projectRoot);

$name = Env::get('ADMIN_NAME', 'Temple Admin') ?? 'Temple Admin';
$email = Env::get('ADMIN_EMAIL', 'admin@ankammathalli.local') ?? 'admin@ankammathalli.local';
$password = Env::get('ADMIN_PASSWORD', 'TempleAdmin123!') ?? 'TempleAdmin123!';
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$statement = $connection->prepare(
    'INSERT INTO users (name, email, password_hash)
     VALUES (:name, :email, :password_hash)
     ON DUPLICATE KEY UPDATE
        name = VALUES(name),
        password_hash = VALUES(password_hash)'
);
$statement->execute([
    'name' => $name,
    'email' => $email,
    'password_hash' => $passwordHash,
]);

echo "Seeded admin user into MySQL.\n";
echo 'Email: ' . $email . "\n";
echo 'Password: ' . $password . "\n";
echo 'Database: ' . (Database::configSummary($projectRoot)['database'] ?? 'unknown') . "\n";
