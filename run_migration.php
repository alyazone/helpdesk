<?php
/**
 * Database Migration Runner
 * Run this file once to set up the multi-unit workflow system
 * Access: http://localhost/helpdesk/run_migration.php
 */

require_once __DIR__ . '/config/config.php';

// Security: Only allow running from localhost
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1', 'localhost'])) {
    die('Access denied. This script can only be run from localhost.');
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Migration</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Multi-Unit Workflow Migration</h1>
";

try {
    $db = getDB();

    // Read migration file
    $migrationFile = __DIR__ . '/migrations/add_multi_unit_workflow.sql';

    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }

    $sql = file_get_contents($migrationFile);

    // Remove comments and split into individual statements
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

    // Split by semicolon but keep USE statements together
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt);
        }
    );

    echo "<h2>Executing Migration...</h2>";
    echo "<pre>";

    $successCount = 0;
    $errorCount = 0;

    foreach ($statements as $statement) {
        if (empty(trim($statement))) continue;

        try {
            $db->exec($statement);
            $successCount++;

            // Show what we're executing (first 100 chars)
            $preview = substr(trim($statement), 0, 100);
            echo "✓ " . htmlspecialchars($preview) . "...\n";

        } catch (PDOException $e) {
            $errorCount++;
            echo "✗ Error: " . htmlspecialchars($e->getMessage()) . "\n";
            echo "   Statement: " . htmlspecialchars(substr($statement, 0, 200)) . "...\n\n";
        }
    }

    echo "</pre>";

    echo "<h2 class='success'>Migration Summary</h2>";
    echo "<p class='success'>Successfully executed: $successCount statements</p>";

    if ($errorCount > 0) {
        echo "<p class='error'>Errors: $errorCount statements</p>";
        echo "<p><strong>Note:</strong> Some errors (like 'table already exists' or 'duplicate entry') are normal if you run this migration multiple times.</p>";
    }

    // Verify test accounts
    echo "<h2>Verifying Test Accounts...</h2>";

    $testEmails = [
        'azri.hanis@jpbdselangor.gov.my' => 'Unit Aduan Dalaman',
        'maznah@jpbdselangor.gov.my' => 'Unit Aset',
        'alia.yusof@jpbdselangor.gov.my' => 'Bahagian Pentadbiran & Kewangan'
    ];

    echo "<ul>";
    foreach ($testEmails as $email => $unitName) {
        $stmt = $db->prepare("SELECT nama_penuh, role, status FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            echo "<li class='success'>✓ $unitName: <strong>" . htmlspecialchars($user['nama_penuh']) . "</strong> (Role: {$user['role']}, Status: {$user['status']})</li>";
        } else {
            echo "<li class='error'>✗ $unitName: User not found!</li>";
        }
    }
    echo "</ul>";

    echo "<h2 class='success'>Migration Complete!</h2>";
    echo "<p><strong>Test Accounts (Password: admin123):</strong></p>";
    echo "<ul>";
    echo "<li>Unit Aduan Dalaman: azri.hanis@jpbdselangor.gov.my</li>";
    echo "<li>Unit Aset: maznah@jpbdselangor.gov.my</li>";
    echo "<li>Bahagian Pentadbiran & Kewangan: alia.yusof@jpbdselangor.gov.my</li>";
    echo "</ul>";

    echo "<p><a href='login.html'>Go to Login Page</a></p>";

    echo "<hr>";
    echo "<p style='color: #666;'><strong>Important:</strong> For security, delete this file (run_migration.php) after running the migration.</p>";

} catch (Exception $e) {
    echo "<h2 class='error'>Migration Failed!</h2>";
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</body></html>";
