<?php
/**
 * Login API
 * Handles user authentication
 */

require_once __DIR__ . '/../config/config.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$email = sanitize($data['email'] ?? '');
$password = $data['password'] ?? '';

// Validation
if (empty($email) || empty($password)) {
    jsonResponse(false, 'Sila lengkapkan semua medan');
}

// Validate email domain
if (!validateEmailDomain($email)) {
    jsonResponse(false, 'Hanya alamat emel jabatan (@jpbdselangor.gov.my) yang dibenarkan');
}

try {
    $db = getDB();

    // Find user by email
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(false, 'Alamat emel tidak sah');
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        jsonResponse(false, 'kata laluan tidak sah');
    }

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama'] = $user['nama_penuh'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    jsonResponse(true, 'Log masuk berjaya', [
        'user' => [
            'id' => $user['id'],
            'nama' => $user['nama_penuh'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);

} catch (PDOException $e) {
    jsonResponse(false, 'Ralat sistem: ' . $e->getMessage());
}
