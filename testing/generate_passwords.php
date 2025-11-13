<?php
/**
 * Generate Password Hashes
 * Run this script once to generate correct password hashes
 */

// Generate hash for admin123
$admin_password = 'admin123';
$admin_hash = password_hash($admin_password, PASSWORD_DEFAULT);

// Generate hash for user123
$user_password = 'user123';
$user_hash = password_hash($user_password, PASSWORD_DEFAULT);

echo "Password Hashes Generated:\n\n";
echo "Admin Password (admin123):\n";
echo $admin_hash . "\n\n";
echo "User Password (user123):\n";
echo $user_hash . "\n\n";

echo "Copy these hashes to update your database:\n\n";
echo "UPDATE users SET password = '$admin_hash' WHERE email = 'admin@jpbdselangor.gov.my';\n";
echo "UPDATE users SET password = '$user_hash' WHERE email = 'ahmad.user@jpbdselangor.gov.my';\n";
?>
