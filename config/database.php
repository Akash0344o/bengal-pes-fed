<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'bpf_admin');
define('DB_PASS', 'securepassword123');
define('DB_NAME', 'bengal_pes_fed');

class Database {
    private static $instance = null;
    
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new PDO(
                "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        }
        return self::$instance;
    }
}

function getDB() {
    return Database::getInstance();
}
?>