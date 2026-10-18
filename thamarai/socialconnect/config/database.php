<?php
/**
 * Database Configuration
 * SocialConnect Platform
 */

// Prevent direct access
if (!defined('SOCIALCONNECT')) {
    die('Direct access not permitted');
}

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'socialconnect');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Database Connection Class
 * Singleton PDO wrapper
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }
    
    /**
     * Get database instance (Singleton)
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     */
    public function getConnection(): PDO {
        return $this->connection;
    }
    
    /**
     * Execute a query with parameters
     */
    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Get single row
     */
    public function fetchOne(string $sql, array $params = []): ?array {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Get all rows
     */
    public function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Insert and return last insert ID
     */
    public function insert(string $sql, array $params = []): int {
        $this->query($sql, $params);
        return (int) $this->connection->lastInsertId();
    }
    
    /**
     * Update and return affected rows
     */
    public function update(string $sql, array $params = []): int {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Delete and return affected rows
     */
    public function delete(string $sql, array $params = []): int {
        return $this->update($sql, $params);
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction(): bool {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit(): bool {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback(): bool {
        return $this->connection->rollBack();
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Helper function to get DB instance
 */
function db(): Database {
    return Database::getInstance();
}
