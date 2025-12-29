<?php
/**
 * Get User Details API
 * Returns full user details including jawatan, bahagian, no_sambungan, tingkat
 */

require_once __DIR__ . '/../config/config.php';

// Accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Invalid request method');
}

// Check if user is logged in
if (!isLoggedIn()) {
    jsonResponse(false, 'Not logged in', ['logged_in' => false]);
}

try {
    $db = getDB();
    $userId = $_SESSION['user_id'];

    // Get full user details from database
    $stmt = $db->prepare("
        SELECT
            id,
            nama_penuh,
            email,
            jawatan,
            bahagian,
            no_sambungan,
            tingkat,
            role
        FROM users
        WHERE id = ? AND status = 'active'
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(false, 'User not found');
    }

    jsonResponse(true, 'User details retrieved successfully', [
        'user' => [
            'id' => $user['id'],
            'nama_penuh' => $user['nama_penuh'],
            'email' => $user['email'],
            'jawatan' => $user['jawatan'] ?? '',
            'bahagian' => $user['bahagian'] ?? '',
            'no_sambungan' => $user['no_sambungan'] ?? '',
            'tingkat' => $user['tingkat'] ?? '',
            'role' => $user['role']
        ]
    ]);

} catch (PDOException $e) {
    error_log('Database error in get_user_details.php: ' . $e->getMessage());
    jsonResponse(false, 'Database error: ' . $e->getMessage());
}
