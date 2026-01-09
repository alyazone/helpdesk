<?php
/**
 * Database Configuration File
 * PLAN Malaysia Selangor - Helpdesk System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure session cookie parameters for better compatibility
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');

    // Set session cookie parameters
    session_set_cookie_params([
        'lifetime' => 0, // Until browser closes
        'path' => '/',
        'domain' => '',
        'secure' => false, // Set to true if using HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();

    // Debug logging
    error_log('Session started. Session ID: ' . session_id());
    error_log('Session data after start: ' . print_r($_SESSION, true));
}

// Database Configuration
define('DB_HOST', 'db');
define('DB_NAME', 'helpdesk_db');
define('DB_USER', 'appuser');
define('DB_PASS', 'apppass');
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

// Email Configuration (SMTP)
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: '');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: '');
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: 'noreply@jpbdselangor.gov.my');
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'PLAN Malaysia Selangor Helpdesk');

// Password Reset Configuration
define('RESET_TOKEN_EXPIRY_HOURS', 1); // Token expires in 1 hour

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

// Check if user is admin (checks active role for access control)
function isAdmin() {
    $activeRole = $_SESSION['active_role'] ?? $_SESSION['role'] ?? '';
    return !empty($activeRole) && $activeRole === 'admin';
}

// Check if user is Unit Aduan Dalaman (checks active role for access control)
function isUnitAduanDalaman() {
    $activeRole = $_SESSION['active_role'] ?? $_SESSION['role'] ?? '';
    return !empty($activeRole) && $activeRole === 'unit_aduan_dalaman';
}

// Check if user is Unit Aset (checks active role for access control)
function isUnitAset() {
    $activeRole = $_SESSION['active_role'] ?? $_SESSION['role'] ?? '';
    return !empty($activeRole) && $activeRole === 'unit_aset';
}

// Check if user is Bahagian Pentadbiran & Kewangan (checks active role for access control)
function isBahagianPentadbiranKewangan() {
    $activeRole = $_SESSION['active_role'] ?? $_SESSION['role'] ?? '';
    return !empty($activeRole) && $activeRole === 'bahagian_pentadbiran_kewangan';
}

// Check if user is Unit IT / Sokongan (checks active role for access control)
function isUnitITSokongan() {
    $activeRole = $_SESSION['active_role'] ?? $_SESSION['role'] ?? '';
    return !empty($activeRole) && $activeRole === 'unit_it_sokongan';
}

// Check if user is Unit Korporat (checks active role for access control)
function isUnitKorporat() {
    $activeRole = $_SESSION['active_role'] ?? $_SESSION['role'] ?? '';
    return !empty($activeRole) && $activeRole === 'unit_korporat';
}

// Check if user is Unit Pentadbiran (checks active role for access control)
function isUnitPentadbiran() {
    $activeRole = $_SESSION['active_role'] ?? $_SESSION['role'] ?? '';
    return !empty($activeRole) && $activeRole === 'unit_pentadbiran';
}

// Check if user has any admin role (checks ORIGINAL role, not active role)
// This determines who can see the role switcher
function hasAdminRole() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], [
        'admin',
        'unit_aduan_dalaman',
        'unit_aset',
        'bahagian_pentadbiran_kewangan',
        'unit_it_sokongan',
        'unit_korporat',
        'unit_pentadbiran'
    ]);
}

/**
 * MULTI-ROLE SUPPORT FUNCTIONS
 */

// Get all roles assigned to a user from the database
function getUserRoles($userId) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT DISTINCT role_name FROM user_roles WHERE user_id = ? ORDER BY role_name");
        $stmt->execute([$userId]);
        $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $roles ?: [];
    } catch (Exception $e) {
        error_log("Error fetching user roles: " . $e->getMessage());
        return [];
    }
}

// Check if user has a specific role (checks from database)
function hasRole($userId, $roleName) {
    $roles = getUserRoles($userId);
    return in_array($roleName, $roles);
}

// Check if user has any of the specified roles
function hasAnyRole($userId, $roleNames) {
    $userRoles = getUserRoles($userId);
    return !empty(array_intersect($roleNames, $userRoles));
}

// Check if active role has access to a specific interface
function canAccessInterface($interface) {
    if (!isLoggedIn()) {
        return false;
    }

    $activeRole = $_SESSION['active_role'] ?? $_SESSION['role'] ?? '';
    $userId = $_SESSION['user_id'] ?? null;

    // Map interfaces to required roles
    $interfaceRoleMap = [
        'admin' => ['admin'],
        'unit_aduan_dalaman' => ['admin', 'unit_aduan_dalaman'],
        'unit_aset' => ['admin', 'unit_aset'],
        'bahagian_pentadbiran_kewangan' => ['admin', 'bahagian_pentadbiran_kewangan'],
        'unit_it_sokongan' => ['admin', 'unit_it_sokongan'],
        'unit_korporat' => ['admin', 'unit_korporat'],
        'unit_pentadbiran' => ['admin', 'unit_pentadbiran'],
        'user' => ['user', 'admin', 'unit_aduan_dalaman', 'unit_aset', 'bahagian_pentadbiran_kewangan', 'unit_it_sokongan', 'unit_korporat', 'unit_pentadbiran']
    ];

    // If interface is not defined, deny access
    if (!isset($interfaceRoleMap[$interface])) {
        return false;
    }

    $requiredRoles = $interfaceRoleMap[$interface];

    // Check if user has any of the required roles
    return hasAnyRole($userId, $requiredRoles) || in_array($activeRole, $requiredRoles);
}

// Get all available roles for role switching
function getAvailableRoles($userId) {
    $roles = getUserRoles($userId);

    // Map database role names to display names
    $roleDisplayNames = [
        'admin' => 'Super Admin',
        'unit_aduan_dalaman' => 'Unit Aduan Dalaman',
        'unit_aset' => 'Unit Aset',
        'bahagian_pentadbiran_kewangan' => 'Pegawai Pelulus',
        'unit_it_sokongan' => 'Unit ICT (Pelaksana)',
        'unit_korporat' => 'Unit Korporat (Laporan)',
        'unit_pentadbiran' => 'Unit Pentadbiran (Pelaksana)',
        'user' => 'Pengguna Biasa'
    ];

    $availableRoles = [];
    foreach ($roles as $role) {
        $availableRoles[] = [
            'value' => $role,
            'label' => $roleDisplayNames[$role] ?? ucfirst(str_replace('_', ' ', $role))
        ];
    }

    return $availableRoles;
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
        'role' => $_SESSION['role'] ?? 'user',
        'active_role' => $_SESSION['active_role'] ?? $_SESSION['role'] ?? 'user'
    ];
}

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

/**
 * Email Functions
 */

/* Load PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send email using PHPMailer
 *
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param string $altBody Plain text alternative body
 * @return bool Success status
 */
function sendEmail($to, $subject, $body, $altBody = '') {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);
        $mail->CharSet = 'UTF-8';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Generate secure random token
 *
 * @param int $length Token length
 * @return string Random token
 */
function generateSecureToken($length = 64) {
    return bin2hex(random_bytes($length));
}

/**
 * Send password reset email
 *
 * @param string $email User email address
 * @param string $token Reset token
 * @param string $nama_penuh User's full name
 * @return bool Success status
 */
function sendPasswordResetEmail($email, $token, $nama_penuh) {
    $resetLink = APP_URL . "/reset-password.html?token=" . urlencode($token);
    $expiryHours = RESET_TOKEN_EXPIRY_HOURS;

    $subject = "Reset Kata Laluan - " . APP_NAME;

    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
            }
            .container {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            .content {
                background: white;
                padding: 30px;
                border-radius: 8px;
            }
            h2 {
                color: #667eea;
                margin-top: 0;
            }
            .button {
                display: inline-block;
                padding: 12px 30px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white !important;
                text-decoration: none;
                border-radius: 5px;
                margin: 20px 0;
                font-weight: bold;
            }
            .warning {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 12px;
                margin: 20px 0;
                border-radius: 4px;
            }
            .footer {
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid #eee;
                font-size: 12px;
                color: #666;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='content'>
                <h2>Reset Kata Laluan Anda</h2>
                <p>Salam {$nama_penuh},</p>
                <p>Kami menerima permintaan untuk menetapkan semula kata laluan akaun anda untuk Sistem Helpdesk PLAN Malaysia Selangor.</p>
                <p>Klik butang di bawah untuk menetapkan semula kata laluan anda:</p>
                <p style='text-align: center;'>
                    <a href='{$resetLink}' class='button'>Reset Kata Laluan</a>
                </p>
                <p>Atau salin dan tampal pautan ini ke dalam pelayar anda:</p>
                <p style='word-break: break-all; background: #f5f5f5; padding: 10px; border-radius: 4px; font-size: 12px;'>
                    {$resetLink}
                </p>
                <div class='warning'>
                    <strong>⚠️ Penting:</strong> Pautan ini akan tamat tempoh dalam <strong>{$expiryHours} jam</strong> atas sebab keselamatan.
                </div>
                <p><strong>Jika anda tidak membuat permintaan ini, sila abaikan emel ini.</strong> Kata laluan anda tidak akan berubah.</p>
                <div class='footer'>
                    <p>Emel ini dihantar secara automatik. Sila jangan balas emel ini.</p>
                    <p>&copy; " . date('Y') . " PLAN Malaysia Selangor. Hak Cipta Terpelihara.</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";

    $altBody = "Salam {$nama_penuh},\n\n"
        . "Kami menerima permintaan untuk menetapkan semula kata laluan akaun anda.\n\n"
        . "Sila lawati pautan berikut untuk menetapkan semula kata laluan anda:\n"
        . "{$resetLink}\n\n"
        . "Pautan ini akan tamat tempoh dalam {$expiryHours} jam.\n\n"
        . "Jika anda tidak membuat permintaan ini, sila abaikan emel ini.\n\n"
        . "Terima kasih,\n"
        . "PLAN Malaysia Selangor Helpdesk";

    return sendEmail($email, $subject, $body, $altBody);
}
