<?php
/**
 * Forgot Password API Endpoint
 * Handles password reset requests
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
    if (!isset($data['email']) || empty($data['email'])) {
        jsonResponse(false, 'Alamat emel diperlukan');
    }

    $email = sanitize($data['email']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, 'Format emel tidak sah');
    }

    // Validate email domain
    if (!validateEmailDomain($email)) {
        jsonResponse(false, 'Sila gunakan alamat emel rasmi @jpbdselangor.gov.my');
    }

    $db = getDB();

    // Check if user exists and is active
    $stmt = $db->prepare("
        SELECT id, nama_penuh, email, status
        FROM users
        WHERE email = ? AND status = 'active'
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Always show success message even if user doesn't exist (security best practice)
    // This prevents enumeration attacks
    if (!$user) {
        jsonResponse(true, 'Jika emel wujud dalam sistem, arahan reset akan dihantar ke emel anda');
    }

    // Generate secure token
    $token = generateSecureToken();

    // Calculate expiry time (1 hour from now)
    $expiryTime = date('Y-m-d H:i:s', strtotime('+' . RESET_TOKEN_EXPIRY_HOURS . ' hours'));

    // Store token in database
    $stmt = $db->prepare("
        UPDATE users
        SET reset_token = ?, reset_token_expires = ?
        WHERE id = ?
    ");
    $stmt->execute([$token, $expiryTime, $user['id']]);

    // Send password reset email
    $emailSent = sendPasswordResetEmail($email, $token, $user['nama_penuh']);

    if (!$emailSent) {
        // Log error but don't expose to user
        error_log("Failed to send password reset email to: {$email}");
        jsonResponse(false, 'Ralat semasa menghantar emel. Sila cuba lagi atau hubungi IT support');
    }

    jsonResponse(true, 'Arahan reset kata laluan telah dihantar ke emel anda. Sila semak folder spam jika tidak menerima emel');

} catch (Exception $e) {
    error_log("Forgot password error: " . $e->getMessage());
    jsonResponse(false, 'Ralat sistem. Sila cuba lagi');
}
