<?php
/**
 * API Endpoint: Manage User Roles
 * Allows administrators to assign/remove roles for users
 */

require_once __DIR__ . '/../../config/config.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Akses ditolak. Hanya pentadbir yang dibenarkan.'
    ]);
    exit;
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        handleGetRoles();
        break;

    case 'POST':
        handlePostRoles();
        break;

    case 'DELETE':
        handleDeleteRole();
        break;

    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
        break;
}

/**
 * GET: Retrieve roles for a user
 */
function handleGetRoles() {
    $userId = $_GET['user_id'] ?? null;

    if (!$userId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'User ID is required'
        ]);
        return;
    }

    try {
        $db = getDB();

        // Get user info
        $stmt = $db->prepare("SELECT id, nama_penuh, email, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'User not found'
            ]);
            return;
        }

        // Get user roles
        $stmt = $db->prepare("
            SELECT ur.id, ur.role_name, ur.assigned_at, u.nama_penuh as assigned_by_name
            FROM user_roles ur
            LEFT JOIN users u ON ur.assigned_by = u.id
            WHERE ur.user_id = ?
            ORDER BY ur.role_name
        ");
        $stmt->execute([$userId]);
        $roles = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'data' => [
                'user' => $user,
                'roles' => $roles
            ]
        ]);

    } catch (Exception $e) {
        error_log("Error fetching user roles: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Ralat sistem: ' . $e->getMessage()
        ]);
    }
}

/**
 * POST: Assign roles to a user
 */
function handlePostRoles() {
    $data = json_decode(file_get_contents('php://input'), true);

    $userId = $data['user_id'] ?? null;
    $roles = $data['roles'] ?? [];

    if (!$userId || !is_array($roles) || empty($roles)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'User ID and roles array are required'
        ]);
        return;
    }

    // Validate role names
    $validRoles = [
        'user',
        'admin',
        'unit_aduan_dalaman',
        'unit_aset',
        'bahagian_pentadbiran_kewangan',
        'unit_it_sokongan',
        'unit_korporat',
        'unit_pentadbiran'
    ];

    foreach ($roles as $role) {
        if (!in_array($role, $validRoles)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "Invalid role: {$role}"
            ]);
            return;
        }
    }

    try {
        $db = getDB();
        $db->beginTransaction();

        // Check if user exists
        $stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        if (!$stmt->fetch()) {
            $db->rollBack();
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'User not found'
            ]);
            return;
        }

        // Delete existing roles for this user
        $stmt = $db->prepare("DELETE FROM user_roles WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Insert new roles
        $currentUserId = $_SESSION['user_id'];
        $stmt = $db->prepare("
            INSERT INTO user_roles (user_id, role_name, assigned_by)
            VALUES (?, ?, ?)
        ");

        foreach ($roles as $role) {
            $stmt->execute([$userId, $role, $currentUserId]);
        }

        // Update user's primary role to the first admin role or 'user'
        $primaryRole = 'user';
        foreach ($roles as $role) {
            if ($role !== 'user') {
                $primaryRole = $role;
                break;
            }
        }

        $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$primaryRole, $userId]);

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Peranan berjaya dikemas kini'
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        error_log("Error assigning user roles: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Ralat sistem: ' . $e->getMessage()
        ]);
    }
}

/**
 * DELETE: Remove a specific role from a user
 */
function handleDeleteRole() {
    $data = json_decode(file_get_contents('php://input'), true);

    $userId = $data['user_id'] ?? null;
    $roleName = $data['role_name'] ?? null;

    if (!$userId || !$roleName) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'User ID and role name are required'
        ]);
        return;
    }

    try {
        $db = getDB();

        // Delete the role
        $stmt = $db->prepare("DELETE FROM user_roles WHERE user_id = ? AND role_name = ?");
        $stmt->execute([$userId, $roleName]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Peranan berjaya dibuang'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Role not found for this user'
            ]);
        }

    } catch (Exception $e) {
        error_log("Error deleting user role: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Ralat sistem: ' . $e->getMessage()
        ]);
    }
}
