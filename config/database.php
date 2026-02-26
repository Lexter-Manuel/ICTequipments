<?php
/**
 * Database Configuration and Connection Handler
 */

class Database {
    private static $instance = null;
    private $connection;
    
    // Database configuration
    private $host = 'localhost';
    private $dbname = 'nia-inventory';
    private $username = 'root';
    private $password = '';
    private $port = 3310; // Ensure this matches your MySQL port
    private $charset = 'utf8mb4';
    
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            // Added the actual error message here to help you debug port/credentials
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    private function __clone() {}
    public function __wakeup() { throw new Exception("Cannot unserialize singleton"); }
}

/**
 * Handles Seeding of the Super Admin
 */
class DatabaseSeeder {
    private $db;
    
    public function __construct($pdo) {
        $this->db = $pdo;
    }
    
    public function ensureSuperAdminExists() {
        try {
            // 1. Check if the table exists first to avoid errors
            $tableCheck = $this->db->query("SHOW TABLES LIKE 'tbl_accounts'");
            if ($tableCheck->rowCount() == 0) {
                error_log("Seeder skipped: tbl_accounts does not exist yet.");
                return;
            }

            // 2. Check if ANY Super Admin exists
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM tbl_accounts WHERE role = ?");
            $stmt->execute(['Super Admin']);
            $result = $stmt->fetch();
            
            if ($result['count'] == 0) {
                $this->createDefaultSuperAdmin();
            }
        } catch (PDOException $e) {
            error_log("Seeding error: " . $e->getMessage());
        }
    }
    
    private function createDefaultSuperAdmin() {
        // Use a static password for initial setup or generate one
        $password = 'niaupriis2026'; 
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $sql = "INSERT INTO tbl_accounts (user_name, email, password, role, status, created_at) 
                VALUES (:user_name, :email, :password, :role, :status, :created_at)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_name'  => 'SystemSuperAdmin',
            ':email'      => 'inventory@upriis.local',
            ':password'   => $hashedPassword,
            ':role'       => 'Super Admin',
            ':status'     => 'Active',
            ':created_at' => date('Y-m-d H:i:s')
        ]);
        
        error_log("Default Super Admin created successfully.");
    }
}

/**
 * Helper function to get database connection
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

/**
 * INITIALIZATION ROUTINE
 * This part runs when the file is included.
 */
try {
    // 1. Establish connection
    $pdo = getDB();
    
    // 2. Run Seeder (Pass the PDO object directly to avoid the loop)
    $seeder = new DatabaseSeeder($pdo);
    $seeder->ensureSuperAdminExists();
    
} catch (Exception $e) {
    error_log("Initialization failed: " . $e->getMessage());
}