<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function env_value(array $keys, string $default = ''): string
{
    foreach ($keys as $key) {
        $value = getenv($key);

        if ($value !== false && trim((string) $value) !== '') {
            return trim((string) $value);
        }

        if (isset($_ENV[$key]) && trim((string) $_ENV[$key]) !== '') {
            return trim((string) $_ENV[$key]);
        }

        if (isset($_SERVER[$key]) && trim((string) $_SERVER[$key]) !== '') {
            return trim((string) $_SERVER[$key]);
        }
    }

    return $default;
}

function file_db_config(): array
{
    $filePath = __DIR__ . DIRECTORY_SEPARATOR . 'db_credentials.php';

    if (!is_file($filePath)) {
        return [];
    }

    $config = require $filePath;
    return is_array($config) ? $config : [];
}

function db_config(): array
{
    $fileConfig = file_db_config();

    return [
        'host' => env_value(['DB_HOST', 'MYSQL_HOST', 'DATABASE_HOST'], (string) ($fileConfig['host'] ?? '127.0.0.1')),
        'port' => (int) env_value(['DB_PORT', 'MYSQL_PORT', 'DATABASE_PORT'], (string) ($fileConfig['port'] ?? '3306')),
        'user' => env_value(['DB_USER', 'MYSQL_USER', 'DATABASE_USER'], (string) ($fileConfig['user'] ?? 'root')),
        'pass' => env_value(['DB_PASS', 'MYSQL_PASSWORD', 'DATABASE_PASSWORD'], (string) ($fileConfig['pass'] ?? '')),
        'name' => env_value(['DB_NAME', 'MYSQL_DATABASE', 'DATABASE_NAME'], (string) ($fileConfig['name'] ?? 'logistics_lms')),
    ];
}

function db_connect(): mysqli
{
    static $conn = null;

    if ($conn instanceof mysqli) {
        return $conn;
    }

    $cfg = db_config();

    try {
        $conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['name'], $cfg['port']);
    } catch (mysqli_sql_exception $e) {
        http_response_code(500);

        if (stripos($e->getMessage(), 'Unknown database') !== false) {
            $sqlFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database.sql';
            $message = 'Database "' . $cfg['name'] . '" does not exist. Import the project SQL file first.';

            if (is_file($sqlFile)) {
                $message .= ' Expected file: ' . $sqlFile;
            }

            exit($message);
        }

        if (stripos($e->getMessage(), 'Access denied for user') !== false) {
            $configFile = __DIR__ . DIRECTORY_SEPARATOR . 'db_credentials.php';
            $message = 'Database login failed for user "' . $cfg['user'] . '" at host "' . $cfg['host'] . '".';
            $message .= ' Set hosting credentials via environment variables or create: ' . $configFile;
            exit($message);
        }

        exit('Database connection failed: ' . $e->getMessage());
    }

    $conn->set_charset('utf8mb4');

    return $conn;
}