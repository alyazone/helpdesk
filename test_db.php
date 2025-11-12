<?php
/**
 * Database Connection Test Script
 * Run this to verify database configuration and connectivity
 */

echo "<h1>Database Connection Test</h1>";
echo "<hr>";

// Test 1: Check if config file exists
echo "<h2>Test 1: Config File</h2>";
if (file_exists(__DIR__ . '/config/config.php')) {
    echo "✓ config.php exists<br>";
    require_once __DIR__ . '/config/config.php';
} else {
    echo "✗ config.php NOT FOUND!<br>";
    exit;
}

// Test 2: Database connection
echo "<h2>Test 2: Database Connection</h2>";
try {
    $db = getDB();
    echo "✓ Database connection successful<br>";
    echo "Database: " . DB_NAME . "<br>";
    echo "Host: " . DB_HOST . "<br>";
} catch (Exception $e) {
    echo "✗ Database connection FAILED!<br>";
    echo "Error: " . $e->getMessage() . "<br>";
    exit;
}

// Test 3: Check if tables exist
echo "<h2>Test 3: Database Tables</h2>";
$tables = ['users', 'officers', 'complaints', 'complaint_status_history', 'attachments', 'notifications'];
foreach ($tables as $table) {
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "✓ Table '$table' exists ($count records)<br>";
    } catch (Exception $e) {
        echo "✗ Table '$table' NOT FOUND or error: " . $e->getMessage() . "<br>";
    }
}

// Test 4: Check users
echo "<h2>Test 4: Users Table</h2>";
try {
    $stmt = $db->query("SELECT id, nama_penuh, email, role, status FROM users");
    $users = $stmt->fetchAll();

    if (count($users) > 0) {
        echo "✓ Found " . count($users) . " users:<br>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['nama_penuh'] . "</td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "<td>" . $user['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    } else {
        echo "✗ No users found in database<br>";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

// Test 5: Check officers
echo "<h2>Test 5: Officers Table</h2>";
try {
    $stmt = $db->query("SELECT id, nama, email, status FROM officers");
    $officers = $stmt->fetchAll();

    if (count($officers) > 0) {
        echo "✓ Found " . count($officers) . " officers:<br>";
        echo "<ul>";
        foreach ($officers as $officer) {
            echo "<li>" . $officer['nama'] . " (" . $officer['status'] . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "✗ No officers found in database<br>";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

// Test 6: Test password verification
echo "<h2>Test 6: Password Verification</h2>";
echo "Testing admin login...<br>";
try {
    $stmt = $db->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->execute(['admin@jpbdselangor.gov.my']);
    $user = $stmt->fetch();

    if ($user) {
        $test_password = 'admin123';
        if (password_verify($test_password, $user['password'])) {
            echo "✓ Admin password verification SUCCESSFUL<br>";
        } else {
            echo "✗ Admin password verification FAILED<br>";
            echo "Password hash in DB: " . substr($user['password'], 0, 30) . "...<br>";
        }
    } else {
        echo "✗ Admin user not found<br>";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

// Test 7: Check uploads directory
echo "<h2>Test 7: Uploads Directory</h2>";
if (file_exists(UPLOAD_DIR)) {
    echo "✓ Uploads directory exists: " . UPLOAD_DIR . "<br>";
    if (is_writable(UPLOAD_DIR)) {
        echo "✓ Uploads directory is writable<br>";
    } else {
        echo "✗ Uploads directory is NOT writable<br>";
    }
} else {
    echo "✗ Uploads directory does NOT exist<br>";
    echo "Creating directory...<br>";
    if (mkdir(UPLOAD_DIR, 0755, true)) {
        echo "✓ Directory created successfully<br>";
    } else {
        echo "✗ Failed to create directory<br>";
    }
}

// Test 8: PHP Configuration
echo "<h2>Test 8: PHP Configuration</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post Max Size: " . ini_get('post_max_size') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . " seconds<br>";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? '✓ Enabled' : '✗ Disabled') . "<br>";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? '✓ Active' : '✗ Not Active') . "<br>";

echo "<hr>";
echo "<h2>Summary</h2>";
echo "If all tests passed with ✓, your system is ready to use!<br>";
echo "If any test failed with ✗, check the error messages above.<br><br>";

echo "<a href='login.html'>Go to Login Page</a> | ";
echo "<a href='v1.html'>Go to Complaint Form</a><br>";
?>
