<?php
/**
 * Fix User Emails and Roles Migration Runner
 * Updates existing emails and assigns roles to all 5 specified users
 * WARNING: This should be removed or protected after running
 */

require_once __DIR__ . '/../../config/config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    die('Access denied. Admins only.');
}

$migrationFile = __DIR__ . '/../../migrations/fix_user_emails_and_roles.sql';

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

    echo '<h2>Migration: Fix User Emails and Assign Roles</h2>';
    echo '<p>Starting migration...</p>';
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
            if (strpos($statement, 'UPDATE users SET email') !== false) {
                preg_match("/email = '([^']+)'/", $statement, $matches);
                if (isset($matches[1])) {
                    echo "<p>✓ Updated email to: {$matches[1]}</p>";
                }
            } elseif (strpos($statement, 'INSERT INTO users') !== false) {
                preg_match("/VALUES.*?'([^']+)'.*?'([^']+)'/", $statement, $matches);
                if (isset($matches[1]) && isset($matches[2])) {
                    echo "<p>✓ Created user: {$matches[1]} ({$matches[2]})</p>";
                }
            }

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

        // Display assigned roles
        echo '<h3>Verified User Roles:</h3>';
        $stmt = $db->query("
            SELECT u.id, u.nama_penuh, u.email, GROUP_CONCAT(ur.role_name ORDER BY ur.role_name SEPARATOR ', ') as roles
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

        echo '<table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">';
        echo '<tr style="background-color: #f0f0f0;">
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Assigned Roles</th>
              </tr>';

        $userCount = 0;
        while ($row = $stmt->fetch()) {
            $userCount++;
            $rolesArray = explode(', ', $row['roles']);
            $roleCount = count($rolesArray);

            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['id']) . '</td>';
            echo '<td><strong>' . htmlspecialchars($row['nama_penuh']) . '</strong></td>';
            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
            echo '<td>';
            if ($row['roles']) {
                echo '<span style="color: green;">(' . $roleCount . ' roles)</span><br>';
                foreach ($rolesArray as $role) {
                    $roleDisplayNames = [
                        'admin' => '👑 Super Admin',
                        'unit_aduan_dalaman' => '📝 Unit Aduan Dalaman',
                        'unit_aset' => '📦 Unit Aset',
                        'bahagian_pentadbiran_kewangan' => '✅ Pegawai Pelulus',
                        'unit_it_sokongan' => '💻 Unit ICT (Pelaksana)',
                        'unit_korporat' => '📊 Unit Korporat (Laporan)',
                        'unit_pentadbiran' => '⚙️ Unit Pentadbiran (Pelaksana)',
                        'user' => '👤 Pengguna Biasa'
                    ];
                    $displayName = $roleDisplayNames[$role] ?? $role;
                    echo '<div style="padding: 3px 0;">' . htmlspecialchars($displayName) . '</div>';
                }
            } else {
                echo '<span style="color: red;">No roles assigned</span>';
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';

        echo '<div style="margin-top: 20px; padding: 15px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;">';
        echo '<h4 style="margin: 0 0 10px 0; color: #155724;">✓ Summary</h4>';
        echo "<p style=\"margin: 0;\"><strong>{$userCount} users</strong> have been configured with their respective roles.</p>";
        echo '<p style="margin: 5px 0 0 0;">All users can now log in and switch between their assigned roles using the role-switcher.</p>';
        echo '</div>';
    }

} catch (Exception $e) {
    echo '<h2 style="color: red;">Migration Failed</h2>';
    echo '<p style="color: red;">' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}

echo '<p style="margin-top: 30px;"><a href="../../admin/index.php" style="padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">Back to Admin Dashboard</a></p>';
?>
