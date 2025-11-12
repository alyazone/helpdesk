# Troubleshooting Guide

## Issue 1: Invalid Password on Login

### Problem
When trying to login with the default credentials, you get "invalid password" error.

### Cause
The password hashes in the database don't match the actual passwords.

### Solution

**Option A: Update passwords in database (RECOMMENDED)**

Run the `fix_passwords.sql` file:

```bash
mysql -u root -p helpdesk_db < fix_passwords.sql
```

Or manually in phpMyAdmin/MySQL:
```sql
USE helpdesk_db;

UPDATE users SET password = '$2y$12$8Wos13KhnYKoWOttGDI28OSaxxrS/oAIhtcczMKtMY5rm0EytQF7i'
WHERE email = 'admin@jpbdselangor.gov.my';

UPDATE users SET password = '$2y$12$0ploemIfcfDq/W/calvyx.oQLVsv12pG8sNr2U4Ci3EDgWroPKJyK'
WHERE email = 'ahmad.user@jpbdselangor.gov.my';
```

**Option B: Re-import database**

1. Drop the existing database:
```sql
DROP DATABASE helpdesk_db;
```

2. Re-import the updated `database.sql`:
```bash
mysql -u root -p < database.sql
```

### Verification

1. Open `test_db.php` in your browser
2. Check "Test 6: Password Verification"
3. Should show: ✓ Admin password verification SUCCESSFUL

---

## Issue 2: Form Submission Success But No Records in Database

### Problem
Form shows success message "Aduan/Cadangan telah berjaya dihantar" but no records are saved in the database.

### Possible Causes

1. **Database connection issue**
2. **PHP session not working**
3. **Database constraints or SQL errors**
4. **Email validation failing**

### Diagnostic Steps

#### Step 1: Run Database Test

Open `test_db.php` in your browser:
```
http://localhost/helpdesk/test_db.php
```

Check if all tests pass with ✓:
- ✓ Database connection
- ✓ All tables exist
- ✓ Users found
- ✓ Officers found

#### Step 2: Check Debug Log

After submitting a form, check the `debug.log` file in the root directory:

```bash
cat debug.log
```

Or view in browser by creating a simple viewer:
```php
<?php
echo "<pre>";
echo file_get_contents('debug.log');
echo "</pre>";
?>
```

Look for:
- "Validation passed" - means data was received
- "Complaint insert executed successfully" - means data was saved
- Any ERROR messages

#### Step 3: Verify Email Domain

Make sure you're using an email with `@jpbdselangor.gov.my` domain in the form.

**Valid**: test@jpbdselangor.gov.my
**Invalid**: test@gmail.com

#### Step 4: Check Required Fields

All these fields are required:
- Jenis (Aduan/Cadangan)
- Perkara
- Keterangan
- Nama Penuh
- Alamat Emel (@jpbdselangor.gov.my)
- Jawatan
- Jenis Aset
- No. Pendaftaran Aset
- Tarikh Kerosakan
- Perihal Kerosakan

#### Step 5: Check Database Directly

After submitting, check if data exists in database:

```sql
USE helpdesk_db;

-- Check complaints count
SELECT COUNT(*) as total_complaints FROM complaints;

-- View latest complaints
SELECT * FROM complaints ORDER BY created_at DESC LIMIT 5;

-- Check if email exists
SELECT * FROM complaints WHERE email = 'your-email@jpbdselangor.gov.my';
```

#### Step 6: Check PHP Error Log

Location depends on your server:

**XAMPP (Windows)**:
```
C:\xampp\php\logs\php_error_log
```

**XAMPP (Linux/Mac)**:
```
/opt/lampp/logs/php_error_log
```

**WAMP**:
```
C:\wamp64\logs\php_error.log
```

#### Step 7: Check Browser Console

1. Open browser Developer Tools (F12)
2. Go to Console tab
3. Submit the form
4. Look for any JavaScript errors or failed API requests
5. Check the Network tab for the API response

---

## Common Errors and Solutions

### Error: "Email domain validation failed"

**Problem**: Using non-government email

**Solution**: Use email ending with `@jpbdselangor.gov.my`

---

### Error: "Database connection failed"

**Problem**: Wrong database credentials

**Solution**:
1. Check `config/config.php`
2. Verify DB_HOST, DB_NAME, DB_USER, DB_PASS
3. Ensure MySQL is running

---

### Error: "SQLSTATE[HY000] [2002] Connection refused"

**Problem**: MySQL server not running

**Solution**:
- XAMPP: Start MySQL from control panel
- WAMP: Start MySQL from taskbar icon
- Linux: `sudo service mysql start`

---

### Error: "SQLSTATE[42S02]: Base table or view not found"

**Problem**: Tables don't exist

**Solution**: Import `database.sql` again:
```bash
mysql -u root -p helpdesk_db < database.sql
```

---

### Error: "Uploads directory not writable"

**Problem**: Permission issue

**Solution**:

**Linux/Mac**:
```bash
chmod 755 uploads
chown www-data:www-data uploads
```

**Windows**:
- Right-click uploads folder
- Properties → Security → Edit
- Give full control to your user

---

## Still Having Issues?

### Create a Test Form Submission

Create `test_submit.php`:

```php
<?php
require_once 'config/config.php';

try {
    $db = getDB();

    // Test data
    $ticket_number = 'TEST-2025-001';
    $stmt = $db->prepare("
        INSERT INTO complaints (
            ticket_number, jenis, perkara, keterangan,
            nama_pengadu, email, jawatan,
            status, progress
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 0)
    ");

    $result = $stmt->execute([
        $ticket_number,
        'aduan',
        'Test Complaint',
        'This is a test complaint',
        'Test User',
        'test@jpbdselangor.gov.my',
        'Tester'
    ]);

    if ($result) {
        echo "✓ Test complaint inserted successfully!<br>";
        echo "Complaint ID: " . $db->lastInsertId() . "<br>";

        // Verify
        $stmt = $db->prepare("SELECT * FROM complaints WHERE ticket_number = ?");
        $stmt->execute([$ticket_number]);
        $complaint = $stmt->fetch();

        echo "<pre>";
        print_r($complaint);
        echo "</pre>";
    } else {
        echo "✗ Failed to insert test complaint<br>";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}
?>
```

Run this file and see if it successfully inserts data.

---

## Quick Fix Checklist

- [ ] Database exists and accessible
- [ ] All tables created (run database.sql)
- [ ] Passwords fixed (run fix_passwords.sql)
- [ ] config.php has correct credentials
- [ ] MySQL service is running
- [ ] uploads/ directory exists and writable
- [ ] Using @jpbdselangor.gov.my email in forms
- [ ] All required fields filled
- [ ] Browser console shows no errors
- [ ] test_db.php shows all tests passing

---

## Contact Support

If you still have issues after trying all the above:

1. Check `debug.log` file
2. Check `test_db.php` output
3. Note any error messages
4. Provide:
   - PHP version
   - MySQL version
   - Operating system
   - Error messages from debug.log
   - Screenshot of browser console errors

---

## Useful Commands

**Check MySQL is running**:
```bash
# Linux
sudo service mysql status

# Mac
brew services list

# Windows (XAMPP)
# Check XAMPP Control Panel
```

**View database structure**:
```bash
mysql -u root -p helpdesk_db -e "SHOW TABLES;"
mysql -u root -p helpdesk_db -e "DESCRIBE complaints;"
```

**Count records**:
```bash
mysql -u root -p helpdesk_db -e "SELECT COUNT(*) FROM complaints;"
mysql -u root -p helpdesk_db -e "SELECT COUNT(*) FROM users;"
```

**Clear debug log**:
```bash
rm debug.log
# or
echo "" > debug.log
```
