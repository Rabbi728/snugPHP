<?php

namespace Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private static $config = null;
    private $connection = null;
    
    private function __construct($config) {
        self::$config = $config;
    }
    
    private function connect() {
        if ($this->connection !== null) {
            return $this->connection;
        }
        
        $dsn = "mysql:host=" . self::$config['host'] . ";dbname=" . self::$config['database'] . ";charset=utf8mb4";
        
        try {
            $this->connection = new PDO($dsn, self::$config['username'], self::$config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
        
        return $this->connection;
    }
    
    public static function init($config) {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }
    
    public static function table($table) {
        if (self::$instance === null) {
            die("Database not initialized. Call Database::init() first.");
        }
        
        $connection = self::$instance->connect();
        return new QueryBuilder($connection, $table);
    }
    
    public static function raw($sql, $params = []) {
        if (self::$instance === null) {
            die("Database not initialized. Call Database::init() first.");
        }
        
        $connection = self::$instance->connect();
        $stmt = $connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}