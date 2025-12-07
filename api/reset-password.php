<?php
/**
 * Reset Password API Endpoint
 * Handles the actual password reset submission
 */

require_once '../config/config.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validate input
    if (!isset($data['token']) || empty($data['token'])) {
        jsonResponse(false, 'Token tidak disediakan');
    }

    if (!isset($data['password']) || empty($data['password'])) {
        jsonResponse(false, 'Kata laluan baru diperlukan');
    }

    if (!isset($data['confirm_password']) || empty($data['confirm_password'])) {
        jsonResponse(false, 'Pengesahan kata laluan diperlukan');
    }

    $token = sanitize($data['token']);
    $password = $data['password']; // Don't sanitize password (preserve special characters)
    $confirmPassword = $data['confirm_password'];

    // Validate password match
    if ($password !== $confirmPassword) {
        jsonResponse(false, 'Kata laluan tidak sepadan');
    }

    // Validate password strength
    if (strlen($password) < 8) {
        jsonResponse(false, 'Kata laluan mesti sekurang-kurangnya 8 aksara');
    }

    // Check for uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        jsonResponse(false, 'Kata laluan mesti mengandungi sekurang-kurangnya 1 huruf besar');
    }

    // Check for lowercase letter
    if (!preg_match('/[a-z]/', $password)) {
        jsonResponse(false, 'Kata laluan mesti mengandungi sekurang-kurangnya 1 huruf kecil');
    }

    // Check for number
    if (!preg_match('/[0-9]/', $password)) {
        jsonResponse(false, 'Kata laluan mesti mengandungi sekurang-kurangnya 1 nombor');
    }

    $db = getDB();

    // Verify token and check expiry
    $stmt = $db->prepare("
        SELECT id, email, nama_penuh, reset_token_expires
        FROM users
        WHERE reset_token = ? AND status = 'active'
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(false, 'Token tidak sah');
    }

    // Check if token has expired
    $currentTime = new DateTime();
    $expiryTime = new DateTime($user['reset_token_expires']);

    if ($currentTime > $expiryTime) {
        // Clear expired token
        $stmt = $db->prepare("
            UPDATE users
            SET reset_token = NULL, reset_token_expires = NULL
            WHERE id = ?
        ");
        $stmt->execute([$user['id']]);

        jsonResponse(false, 'Token telah tamat tempoh. Sila minta pautan reset baru');
    }

    // Hash the new password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Update password and clear reset token
    $stmt = $db->prepare("
        UPDATE users
        SET password = ?,
            reset_token = NULL,
            reset_token_expires = NULL,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    $stmt->execute([$hashedPassword, $user['id']]);

    // Send confirmation email (optional but recommended)
    $subject = "Kata Laluan Telah Dikemaskini - " . APP_NAME;
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
            }
            .container {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 30px;
                border-radius: 10px;
            }
            .content {
                background: white;
                padding: 30px;
                border-radius: 8px;
            }
            h2 {
                color: #667eea;
            }
            .success-box {
                background: #d1fae5;
                border-left: 4px solid #10b981;
                padding: 12px;
                margin: 20px 0;
                border-radius: 4px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='content'>
                <h2>Kata Laluan Berjaya Dikemaskini</h2>
                <p>Salam {$user['nama_penuh']},</p>
                <p>Kata laluan akaun anda untuk Sistem Helpdesk PLAN Malaysia Selangor telah berjaya dikemaskini.</p>
                <div class='success-box'>
                    <strong>✓ Kata laluan anda telah ditukar pada:</strong><br>
                    " . date('d/m/Y, h:i A') . "
                </div>
                <p>Anda kini boleh log masuk menggunakan kata laluan baru anda.</p>
                <p><strong>Jika anda tidak membuat perubahan ini</strong>, sila hubungi bahagian IT dengan segera di <strong>03-5511 8888</strong>.</p>
                <p style='margin-top: 30px;'>Terima kasih,<br>PLAN Malaysia Selangor Helpdesk</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $altBody = "Salam {$user['nama_penuh']},\n\n"
        . "Kata laluan akaun anda untuk Sistem Helpdesk PLAN Malaysia Selangor telah berjaya dikemaskini.\n\n"
        . "Kata laluan ditukar pada: " . date('d/m/Y, h:i A') . "\n\n"
        . "Anda kini boleh log masuk menggunakan kata laluan baru anda.\n\n"
        . "Jika anda tidak membuat perubahan ini, sila hubungi bahagian IT dengan segera di 03-5511 8888.\n\n"
        . "Terima kasih,\n"
        . "PLAN Malaysia Selangor Helpdesk";

    sendEmail($user['email'], $subject, $body, $altBody);

    jsonResponse(true, 'Kata laluan anda telah berjaya dikemaskini. Anda akan dibawa ke halaman log masuk');

} catch (Exception $e) {
    error_log("Reset password error: " . $e->getMessage());
    jsonResponse(false, 'Ralat sistem. Sila cuba lagi');
}
