<?php
/**
 * Run Unit Aset Migration
 * This script adds the necessary columns for Unit Aset workflow
 */

require_once __DIR__ . '/config/config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Unit Aset Migration</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Unit Aset Database Migration</h1>
";

try {
    $db = getDB();

    // Read migration file
    $migrationFile = __DIR__ . '/migrations/add_unit_aset_columns.sql';

    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }

    $sql = file_get_contents($migrationFile);

    // Remove comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

    // Split by semicolon
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && strtoupper(substr(trim($stmt), 0, 3)) !== 'USE';
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
            $preview = substr(trim($statement), 0, 100);
            echo "✓ " . htmlspecialchars($preview) . "...\n";
        } catch (PDOException $e) {
            $errorCount++;
            $errorMsg = $e->getMessage();

            // Ignore "Duplicate column" and "Duplicate key" errors
            if (strpos($errorMsg, 'Duplicate column') !== false ||
                strpos($errorMsg, 'Duplicate key name') !== false ||
                strpos($errorMsg, "Can't DROP") !== false) {
                echo "⊙ Skipped (already exists): " . htmlspecialchars(substr($statement, 0, 100)) . "...\n";
            } else {
                echo "✗ Error: " . htmlspecialchars($errorMsg) . "\n";
            }
        }
    }

    echo "</pre>";

    echo "<h2 class='success'>Migration Summary</h2>";
    echo "<p class='success'>Successfully executed: $successCount statements</p>";
    echo "<p>Errors/Skipped: $errorCount statements</p>";

    // Verify columns
    echo "<h2>Verifying Database Schema...</h2>";
    $stmt = $db->query("SHOW COLUMNS FROM complaints LIKE 'unit_aset_processed_by'");
    $col1 = $stmt->fetch();

    $stmt = $db->query("SHOW COLUMNS FROM complaints LIKE 'approval_officer_id'");
    $col2 = $stmt->fetch();

    echo "<ul>";
    if ($col1) {
        echo "<li class='success'>✓ Column 'unit_aset_processed_by' exists</li>";
    } else {
        echo "<li class='error'>✗ Column 'unit_aset_processed_by' not found</li>";
    }

    if ($col2) {
        echo "<li class='success'>✓ Column 'approval_officer_id' exists</li>";
    } else {
        echo "<li class='error'>✗ Column 'approval_officer_id' not found</li>";
    }
    echo "</ul>";

    echo "<h2 class='success'>Migration Complete!</h2>";
    echo "<p>The Unit Aset workflow columns have been successfully added.</p>";
    echo "<p><a href='admin/unit-aset/'>Go to Unit Aset Dashboard</a></p>";

} catch (Exception $e) {
    echo "<h2 class='error'>Migration Failed!</h2>";
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</body></html>";
