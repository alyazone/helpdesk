<?php
/**
 * Migration Runner
 * Runs the multi-role support migration
 * WARNING: This should be removed or protected after running
 */

require_once __DIR__ . '/../../config/config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    die('Access denied. Admins only.');
}

$migrationFile = __DIR__ . '/../../migrations/add_multi_role_support.sql';

if (!file_exists($migrationFile)) {
    die('Migration file not found');
}

$sqlContent = file_get_contents($migrationFile);

// Split by semicolon and execute each statement
$statements = explode(';', $sqlContent);

try {
    $db = getDB();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $successCount = 0;
    $errors = [];

    foreach ($statements as $statement) {
        $statement = trim($statement);

        // Skip empty statements and comments
        if (empty($statement) || substr($statement, 0, 2) === '--') {
            continue;
        }

        try {
            $db->exec($statement);
            $successCount++;
        } catch (PDOException $e) {
            // Ignore duplicate key errors (role already exists)
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                continue;
            }
            $errors[] = [
                'statement' => substr($statement, 0, 100) . '...',
                'error' => $e->getMessage()
            ];
        }
    }

    echo '<h2>Migration Results</h2>';
    echo "<p><strong>Successfully executed:</strong> {$successCount} statements</p>";

    if (!empty($errors)) {
        echo '<h3>Errors:</h3>';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>';
            echo '<strong>Statement:</strong> ' . htmlspecialchars($error['statement']) . '<br>';
            echo '<strong>Error:</strong> ' . htmlspecialchars($error['error']);
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p style="color: green;">✓ Migration completed successfully!</p>';

        // Display assigned roles
        echo '<h3>Assigned Roles:</h3>';
        $stmt = $db->query("
            SELECT u.nama_penuh, u.email, GROUP_CONCAT(ur.role_name ORDER BY ur.role_name SEPARATOR ', ') as roles
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            WHERE u.email IN (
                'norhayati@jpbdselangor.gov.my',
                'alia@jpbdselangor.gov.my',
                'azri@jpbdselangor.gov.my',
                'maznah@jpbdselangor.gov.my',
                'adzhan@jpbdselangor.gov.my'
            )
            GROUP BY u.id, u.nama_penuh, u.email
            ORDER BY u.nama_penuh
        ");

        echo '<table border="1" cellpadding="10" style="border-collapse: collapse;">';
        echo '<tr><th>Name</th><th>Email</th><th>Assigned Roles</th></tr>';
        while ($row = $stmt->fetch()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['nama_penuh']) . '</td>';
            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
            echo '<td>' . htmlspecialchars($row['roles'] ?: 'No roles assigned') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

} catch (Exception $e) {
    echo '<h2>Migration Failed</h2>';
    echo '<p style="color: red;">' . htmlspecialchars($e->getMessage()) . '</p>';
}

echo '<p><a href="../../admin/index.php">Back to Admin Dashboard</a></p>';
