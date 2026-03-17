<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const DB_HOST = '127.0.0.1';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'logistics_lms';

function db_connect(): mysqli
{
    static $conn = null;

    if ($conn instanceof mysqli) {
        return $conn;
    }

    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    } catch (mysqli_sql_exception $e) {
        http_response_code(500);

        if (stripos($e->getMessage(), 'Unknown database') !== false) {
            $sqlFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database.sql';
            $message = 'Database "' . DB_NAME . '" does not exist. Import the project SQL file first.';

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