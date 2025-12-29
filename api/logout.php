<?php
/**
 * Logout API
 * Handles user logout
 */

require_once __DIR__ . '/../config/config.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // For GET requests, just redirect
    session_unset();
    session_destroy();
    redirect('../login.html');
}

// Destroy session
session_unset();
session_destroy();

// Return JSON response
jsonResponse(true, 'Log keluar berjaya');
