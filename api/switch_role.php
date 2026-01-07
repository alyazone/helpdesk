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

// Validate requested role
$allowedRoles = ['user', $_SESSION['role']]; // Can switch to 'user' or back to original role

if (!in_array($requestedRole, $allowedRoles)) {
    jsonResponse(false, 'Peranan yang diminta tidak sah');
}

// Switch the active role
$_SESSION['active_role'] = $requestedRole;

// Log the role switch for audit purposes
error_log("User {$_SESSION['email']} switched role to: {$requestedRole}");

// Return success response
jsonResponse(true, 'Peranan berjaya ditukar', [
    'active_role' => $_SESSION['active_role'],
    'original_role' => $_SESSION['role']
]);
