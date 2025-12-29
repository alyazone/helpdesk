<?php
/**
 * API - Manage Users (Admin Only)
 */

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    jsonResponse(false, 'Akses ditolak');
}

$db = getDB();

// Determine action
$action = $_POST['action'] ?? 'save';

try {
    if ($action === 'delete') {
        // Delete user
        $user_id = intval($_POST['user_id'] ?? 0);

        if ($user_id <= 0) {
            jsonResponse(false, 'ID pengguna tidak sah');
        }

        // Prevent deleting yourself
        if ($user_id == $_SESSION['user_id']) {
            jsonResponse(false, 'Anda tidak boleh memadam akaun anda sendiri');
        }

        // Check if user exists
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            jsonResponse(false, 'Pengguna tidak dijumpai');
        }

        // Delete user
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        jsonResponse(true, 'Pengguna berjaya dipadam');

    } else {
        // Add or update user
        $user_id = intval($_POST['user_id'] ?? 0);
        $nama_penuh = sanitize($_POST['nama_penuh']);
        $email = sanitize($_POST['email']);
        $password = $_POST['password'] ?? '';
        $jawatan = sanitize($_POST['jawatan'] ?? '');
        $no_sambungan = sanitize($_POST['no_sambungan'] ?? '');
        $bahagian = sanitize($_POST['bahagian'] ?? '');
        $role = sanitize($_POST['role']);
        $status = sanitize($_POST['status']);

        // Validate required fields
        if (empty($nama_penuh) || empty($email) || empty($role) || empty($status)) {
            jsonResponse(false, 'Sila lengkapkan semua medan yang diperlukan');
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(false, 'Format email tidak sah');
        }

        // Validate role
        if (!in_array($role, ['user', 'admin', 'staff'])) {
            jsonResponse(false, 'Peranan tidak sah');
        }

        // Validate status
        if (!in_array($status, ['active', 'inactive'])) {
            jsonResponse(false, 'Status tidak sah');
        }

        if ($user_id > 0) {
            // Update existing user
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $existing_user = $stmt->fetch();

            if (!$existing_user) {
                jsonResponse(false, 'Pengguna tidak dijumpai');
            }

            // Check if email is already used by another user
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                jsonResponse(false, 'Email telah digunakan oleh pengguna lain');
            }

            // Build update query
            $update_fields = [
                'nama_penuh' => $nama_penuh,
                'email' => $email,
                'jawatan' => $jawatan,
                'no_sambungan' => $no_sambungan,
                'bahagian' => $bahagian,
                'role' => $role,
                'status' => $status
            ];

            // Add password to update if provided
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    jsonResponse(false, 'Kata laluan mestilah sekurang-kurangnya 6 aksara');
                }
                $update_fields['password'] = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            }

            $sql = "UPDATE users SET ";
            $sql_parts = [];
            $params = [];
            foreach ($update_fields as $field => $value) {
                $sql_parts[] = "$field = ?";
                $params[] = $value;
            }
            $sql .= implode(', ', $sql_parts);
            $sql .= " WHERE id = ?";
            $params[] = $user_id;

            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            jsonResponse(true, 'Pengguna berjaya dikemaskini');

        } else {
            // Create new user
            // Validate password for new user
            if (empty($password)) {
                jsonResponse(false, 'Kata laluan diperlukan untuk pengguna baru');
            }

            if (strlen($password) < 6) {
                jsonResponse(false, 'Kata laluan mestilah sekurang-kurangnya 6 aksara');
            }

            // Check if email already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                jsonResponse(false, 'Email telah digunakan');
            }

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            // Insert new user
            $stmt = $db->prepare("
                INSERT INTO users (nama_penuh, email, password, jawatan, no_sambungan, bahagian, role, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $nama_penuh,
                $email,
                $hashed_password,
                $jawatan,
                $no_sambungan,
                $bahagian,
                $role,
                $status
            ]);

            jsonResponse(true, 'Pengguna baru berjaya ditambah');
        }
    }
} catch (Exception $e) {
    jsonResponse(false, 'Ralat: ' . $e->getMessage());
}
