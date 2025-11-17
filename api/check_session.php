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

// Debug: Log session data
error_log('Session ID: ' . session_id());
error_log('Session data: ' . print_r($_SESSION, true));
error_log('Is logged in: ' . (isLoggedIn() ? 'yes' : 'no'));

// Check if user is logged in
if (!isLoggedIn()) {
    jsonResponse(false, 'Not logged in', ['logged_in' => false]);
}

// Get user data from session
$user = getUser();

// Debug: Log session data
error_log('Session ID: ' . session_id());
error_log('Session data: ' . print_r($_SESSION, true));
error_log('Is logged in: ' . (isLoggedIn() ? 'yes' : 'no'));

jsonResponse(true, 'User is logged in', [
    'logged_in' => true,
    'user' => $user
]);
