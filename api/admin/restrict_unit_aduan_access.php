<?php
/**
 * Restrict Unit Aduan Dalaman Access Migration Runner
 * Ensures only Norhayati can access Unit Aduan Dalaman
 * WARNING: This should be removed or protected after running
 */

require_once __DIR__ . '/../../config/config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    die('Access denied. Admins only.');
}

$migrationFile = __DIR__ . '/../../migrations/restrict_unit_aduan_access.sql';

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

    echo '<h2>Migration: Restrict Unit Aduan Dalaman Access</h2>';
    echo '<p>Ensuring only Norhayati can access Unit Aduan Dalaman...</p>';
    echo '<hr>';

    foreach ($statements as $statement) {
        $statement = trim($statement);

        // Skip empty statements and comments
        if (empty($statement) || substr($statement, 0, 2) === '--') {
            continue;
        }

        try {
            $db->exec($statement);
            $successCount++;

            // Show progress for key operations
            if (strpos($statement, "email = 'azri@jpbdselangor.gov.my'") !== false) {
                echo "<p>✓ Updated Azri's primary role to 'unit_aset'</p>";
            } elseif (strpos($statement, "email = 'norhayati@jpbdselangor.gov.my'") !== false) {
                echo "<p>✓ Updated Norhayati's primary role to 'admin'</p>";
            } elseif (strpos($statement, 'DELETE FROM user_roles') !== false) {
                echo "<p>✓ Removed any unit_aduan_dalaman role from Azri</p>";
            }

        } catch (PDOException $e) {
            // Ignore if no rows were deleted (role didn't exist)
            if (strpos($statement, 'DELETE') === false) {
                $errors[] = [
                    'statement' => substr($statement, 0, 100) . '...',
                    'error' => $e->getMessage()
                ];
            }
        }
    }

    echo '<hr>';
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
        echo '<p style="color: green; font-size: 18px; font-weight: bold;">✓ Migration completed successfully!</p>';

        // Verify access control
        echo '<h3>Verification: Unit Aduan Dalaman Access</h3>';

        // Check who has unit_aduan_dalaman in user_roles table
        $stmt = $db->query("
            SELECT u.nama_penuh, u.email, u.role as primary_role
            FROM users u
            INNER JOIN user_roles ur ON u.id = ur.user_id
            WHERE ur.role_name = 'unit_aduan_dalaman'
            ORDER BY u.nama_penuh
        ");

        $usersWithAccess = $stmt->fetchAll();

        echo '<table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">';
        echo '<tr style="background-color: #d4edda;">
                <th>Name</th>
                <th>Email</th>
                <th>Primary Role</th>
              </tr>';

        if (count($usersWithAccess) > 0) {
            foreach ($usersWithAccess as $user) {
                echo '<tr>';
                echo '<td><strong>' . htmlspecialchars($user['nama_penuh']) . '</strong></td>';
                echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                echo '<td>' . htmlspecialchars($user['primary_role']) . '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="3" style="text-align: center; color: red;">No users have unit_aduan_dalaman role</td></tr>';
        }
        echo '</table>';

        // Check Azri's current roles
        echo '<h3>Azri\'s Current Roles:</h3>';
        $stmt = $db->query("
            SELECT u.nama_penuh, u.email, u.role as primary_role, GROUP_CONCAT(ur.role_name ORDER BY ur.role_name SEPARATOR ', ') as all_roles
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            WHERE u.email = 'azri@jpbdselangor.gov.my'
            GROUP BY u.id
        ");

        $azriData = $stmt->fetch();

        if ($azriData) {
            echo '<table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">';
            echo '<tr style="background-color: #f0f0f0;">
                    <th>Name</th>
                    <th>Email</th>
                    <th>Primary Role</th>
                    <th>All Assigned Roles</th>
                  </tr>';
            echo '<tr>';
            echo '<td><strong>' . htmlspecialchars($azriData['nama_penuh']) . '</strong></td>';
            echo '<td>' . htmlspecialchars($azriData['email']) . '</td>';
            echo '<td><span style="color: green;">' . htmlspecialchars($azriData['primary_role']) . '</span></td>';
            echo '<td>' . htmlspecialchars($azriData['all_roles']) . '</td>';
            echo '</tr>';
            echo '</table>';

            // Check if unit_aduan_dalaman is in the list
            if (strpos($azriData['all_roles'], 'unit_aduan_dalaman') !== false) {
                echo '<div style="margin-top: 20px; padding: 15px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;">';
                echo '<h4 style="margin: 0 0 10px 0; color: #721c24;">⚠️ Warning</h4>';
                echo '<p style="margin: 0;">Azri still has unit_aduan_dalaman role! Please run this migration again.</p>';
                echo '</div>';
            } else {
                echo '<div style="margin-top: 20px; padding: 15px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;">';
                echo '<h4 style="margin: 0 0 10px 0; color: #155724;">✓ Success</h4>';
                echo '<p style="margin: 0;"><strong>Azri does NOT have unit_aduan_dalaman role.</strong> Only Norhayati can access Unit Aduan Dalaman interface.</p>';
                echo '</div>';
            }
        }
    }

} catch (Exception $e) {
    echo '<h2 style="color: red;">Migration Failed</h2>';
    echo '<p style="color: red;">' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}

echo '<p style="margin-top: 30px;"><a href="../../admin/index.php" style="padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">Back to Admin Dashboard</a></p>';
?>
