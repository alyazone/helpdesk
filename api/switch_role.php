<?php
session_start();
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    jsonResponse(false, 'Anda perlu log masuk untuk menukar peranan');
}

// Only allow users with admin roles to switch roles
if (!hasAdminRole()) {
    jsonResponse(false, 'Anda tidak mempunyai kebenaran untuk menukar peranan');
}

// Get the requested role from POST data
$data = json_decode(file_get_contents('php://input'), true);
$requestedRole = $data['role'] ?? '';

// Get all user roles from database
$userId = $_SESSION['user_id'];
$userRoles = getUserRoles($userId);

// If no roles in database, fall back to user's primary role
if (empty($userRoles)) {
    $userRoles = [$_SESSION['role']];
}

// Store updated roles in session
$_SESSION['user_roles'] = $userRoles;

// Validate requested role - must be one of the user's assigned roles
if (!in_array($requestedRole, $userRoles)) {
    jsonResponse(false, 'Peranan yang diminta tidak sah atau tidak diberikan kepada anda');
}

// Switch the active role
$_SESSION['active_role'] = $requestedRole;

// Log the role switch for audit purposes
error_log("User {$_SESSION['email']} switched role to: {$requestedRole}");

// Return success response
jsonResponse(true, 'Peranan berjaya ditukar', [
    'active_role' => $_SESSION['active_role'],
    'original_role' => $_SESSION['role'],
    'available_roles' => getAvailableRoles($userId)
]);
