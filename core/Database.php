<?php

namespace Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct($config) {
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
        
        try {
            $this->connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function connect($config) {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }
    
    public static function table($table) {
        return new QueryBuilder(self::$instance->connection, $table);
    }
    
    public function raw($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}