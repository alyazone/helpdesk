<?php
/**
 * Registration API
 * Handles new user registration
 */

require_once __DIR__ . '/../config/config.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$nama_penuh = sanitize($data['nama_penuh'] ?? '');
$email = sanitize($data['email'] ?? '');
$password = $data['password'] ?? '';
$password_confirm = $data['password_confirm'] ?? '';
$jawatan = sanitize($data['jawatan'] ?? '');
$bahagian = sanitize($data['bahagian'] ?? '');
$no_sambungan = sanitize($data['no_sambungan'] ?? '');
$tingkat = sanitize($data['tingkat'] ?? '');

/* Validation */
if (empty($nama_penuh) || empty($email) || empty($password) || empty($password_confirm)) {
    jsonResponse(false, 'Sila lengkapkan semua medan yang diperlukan');
}

// Validate email domain
if (!validateEmailDomain($email)) {
    jsonResponse(false, 'Hanya alamat emel jabatan (@jpbdselangor.gov.my) yang dibenarkan');
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Format alamat emel tidak sah');
}

// Check password match
if ($password !== $password_confirm) {
    jsonResponse(false, 'Kata laluan tidak sepadan');
}

// Password strength validation
if (strlen($password) < 8) {
    jsonResponse(false, 'Kata laluan mestilah sekurang-kurangnya 8 aksara');
}

try {
    $db = getDB();

    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        jsonResponse(false, 'Alamat emel telah digunakan');
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $db->prepare("
        INSERT INTO users (nama_penuh, email, password, jawatan, bahagian, no_sambungan, tingkat, role, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'user', 'active')
    ");

    $stmt->execute([
        $nama_penuh,
        $email,
        $hashed_password,
        $jawatan,
        $bahagian,
        $no_sambungan,
        $tingkat
    ]);

    $user_id = $db->lastInsertId();

    // Auto login after registration
    $_SESSION['user_id'] = $user_id;
    $_SESSION['nama'] = $nama_penuh;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = 'user';

    jsonResponse(true, 'Pendaftaran berjaya', [
        'user' => [
            'id' => $user_id,
            'nama' => $nama_penuh,
            'email' => $email,
            'role' => 'user'
        ]
    ]);

} catch (PDOException $e) {
    jsonResponse(false, 'Ralat sistem: ' . $e->getMessage());
}
