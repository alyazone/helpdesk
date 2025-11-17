<?php
/**
 * Check Session API
 * Returns current user session information
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

// Get user data from session
$user = getUser();

jsonResponse(true, 'User is logged in', [
    'logged_in' => true,
    'user' => $user
]);
