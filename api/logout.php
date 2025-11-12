<?php
/**
 * Logout API
 * Handles user logout
 */

require_once __DIR__ . '/../config/config.php';

// Destroy session
session_unset();
session_destroy();

// If it's an AJAX request, return JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    jsonResponse(true, 'Log keluar berjaya');
} else {
    // Otherwise redirect to login page
    redirect('../login.html');
}
