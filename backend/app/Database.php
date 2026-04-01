<?php

class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance === null) {
            try {
                $cfg = require __DIR__ . '/../config/database.php';

                $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset=utf8mb4";

                self::$instance = new PDO(
                    $dsn,
                    $cfg['username'],
                    $cfg['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );

            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    "error" => "Database connection failed",
                    "message" => $e->getMessage()
                ]);
                exit;
            }
        }
        return self::$instance;
    }
}