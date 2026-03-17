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
    }

    return $default;
}

function db_config(): array
{
    return [
        'host' => env_value(['DB_HOST', 'MYSQL_HOST', 'DATABASE_HOST'], '127.0.0.1'),
        'port' => (int) env_value(['DB_PORT', 'MYSQL_PORT', 'DATABASE_PORT'], '3306'),
        'user' => env_value(['DB_USER', 'MYSQL_USER', 'DATABASE_USER'], 'root'),
        'pass' => env_value(['DB_PASS', 'MYSQL_PASSWORD', 'DATABASE_PASSWORD'], ''),
        'name' => env_value(['DB_NAME', 'MYSQL_DATABASE', 'DATABASE_NAME'], 'logistics_lms'),
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

        exit('Database connection failed: ' . $e->getMessage());
    }

    $conn->set_charset('utf8mb4');

    return $conn;
}