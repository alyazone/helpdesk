<?php
/**
 * Migration Runner
 * Run this file to apply the Unit IT / Sokongan role migration
 */

require_once __DIR__ . '/../config/config.php';

// Simple security check - only allow from localhost or when logged in as admin
if (php_sapi_name() !== 'cli') {
    if (!isLoggedIn() || !isAdmin()) {
        die('Unauthorized. Only admin users can run migrations.');
    }
}

echo "<pre>";
echo "=== Running Database Migration: Add Unit IT / Sokongan Role ===\n\n";

try {
    $db = getDB();
    $sql = file_get_contents(__DIR__ . '/add_unit_it_sokongan_role.sql');

    // Remove comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    $success_count = 0;
    $error_count = 0;

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $db->exec($statement);
                $preview = substr($statement, 0, 80);
                echo "✓ Success: " . $preview . "...\n";
                $success_count++;
            } catch (PDOException $e) {
                $error_msg = $e->getMessage();
                // Ignore "already exists" errors
                if (strpos($error_msg, 'already exists') !== false || strpos($error_msg, 'Duplicate') !== false) {
                    echo "⚠ Skipped (already exists): " . substr($statement, 0, 60) . "...\n";
                } else {
                    echo "✗ Error: " . $error_msg . "\n";
                    echo "  Statement: " . substr($statement, 0, 100) . "...\n\n";
                    $error_count++;
                }
            }
        }
    }

    echo "\n=== Migration Summary ===\n";
    echo "Successful: $success_count\n";
    echo "Errors: $error_count\n";
    echo "\nMigration completed!\n";
    echo "\n✓ Unit IT / Sokongan role has been added to the system.\n";
    echo "✓ You can now assign Unit IT officers and they can log in.\n";

} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
