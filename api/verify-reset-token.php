<?php
/**
 * Verify Reset Token API Endpoint
 * Verifies if a password reset token is valid and not expired
 */

require_once '../config/config.php';

header('Content-Type: application/json');

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed');
}

try {
    // Get token from query parameter
    if (!isset($_GET['token']) || empty($_GET['token'])) {
        jsonResponse(false, 'Token tidak disediakan');
    }

    $token = sanitize($_GET['token']);

    $db = getDB();

    // Check if token exists and is not expired
    $stmt = $db->prepare("
        SELECT id, email, nama_penuh, reset_token_expires
        FROM users
        WHERE reset_token = ? AND status = 'active'
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    // Check if token exists
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

    // Token is valid
    jsonResponse(true, 'Token sah', [
        'email' => $user['email'],
        'nama_penuh' => $user['nama_penuh']
    ]);

} catch (Exception $e) {
    error_log("Token verification error: " . $e->getMessage());
    jsonResponse(false, 'Ralat sistem. Sila cuba lagi');
}
