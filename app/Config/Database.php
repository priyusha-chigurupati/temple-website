<?php

declare(strict_types=1);

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(string $projectRoot): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        Env::load($projectRoot . '/.env');

        $host = Env::get('DB_HOST', '127.0.0.1');
        $port = Env::get('DB_PORT', '3306');
        $name = Env::get('DB_NAME');
        $user = Env::get('DB_USER');
        $password = Env::get('DB_PASS', '');
        $charset = Env::get('DB_CHARSET', 'utf8mb4');

        if ($name === null || $user === null) {
            throw new RuntimeException('Database credentials are missing. Set DB_NAME and DB_USER in .env.');
        }

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $name, $charset);

        self::$connection = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$connection;
    }

    public static function configSummary(string $projectRoot): array
    {
        Env::load($projectRoot . '/.env');

        return [
            'host' => Env::get('DB_HOST', '127.0.0.1'),
            'port' => Env::get('DB_PORT', '3306'),
            'database' => Env::get('DB_NAME'),
            'user' => Env::get('DB_USER'),
            'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
            'default_locale' => Env::get('APP_DEFAULT_LOCALE', 'en'),
            'supported_locales' => Env::get('APP_SUPPORTED_LOCALES', 'en,te'),
        ];
    }
}
