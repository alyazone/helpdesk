<?php
/**
 * Database Configuration File
 * PLAN Malaysia Selangor - Helpdesk System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'helpdesk_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'PLAN Malaysia Selangor - Sistem Helpdesk');
define('APP_URL', 'http://localhost/helpdesk');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes

// Allowed file types for upload
define('ALLOWED_FILE_TYPES', [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
]);

define('ALLOWED_FILE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Email domain restriction
define('ALLOWED_EMAIL_DOMAIN', 'jpbdselangor.gov.my');

// Timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Database Connection Class
 */
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}

/**
 * Helper Functions
 */

// Get database connection
function getDB() {
    return Database::getInstance()->getConnection();
}

// Sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirect function
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Generate ticket number
function generateTicketNumber() {
    $year = date('Y');
    $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
    return "ADU-{$year}-{$random}";
}

// Validate email domain
function validateEmailDomain($email) {
    $domain = substr(strrchr($email, "@"), 1);
    return $domain === ALLOWED_EMAIL_DOMAIN;
}

// Format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Send JSON response
function jsonResponse($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Get user data from session
function getUser() {
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'] ?? null,
        'nama' => $_SESSION['nama'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'role' => $_SESSION['role'] ?? 'user'
    ];
}

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
