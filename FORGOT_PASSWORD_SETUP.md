# Forgot Password Feature - Setup Guide

This document provides instructions for setting up and configuring the Forgot Password feature for the PLAN Malaysia Selangor Helpdesk System.

## Overview

The Forgot Password feature allows users to securely reset their passwords via email. The system generates a secure token, sends it via email, and allows users to reset their password within a specified time window (1 hour by default).

## Features

- ✅ Secure token-based password reset
- ✅ Email notifications with reset links
- ✅ Token expiration (1 hour default)
- ✅ Password strength validation
- ✅ Email domain validation (@jpbdselangor.gov.my)
- ✅ Prevention of user enumeration attacks
- ✅ Confirmation email after password reset

## Files Created/Modified

### New Files
1. **Frontend Pages:**
   - `/forgot-password.html` - Password reset request page
   - `/reset-password.html` - New password submission page

2. **API Endpoints:**
   - `/api/forgot-password.php` - Handles password reset requests
   - `/api/verify-reset-token.php` - Validates reset tokens
   - `/api/reset-password.php` - Processes password resets

3. **Configuration:**
   - `/.env.example` - Email configuration template
   - `/migrations/add_password_reset_fields.sql` - Database migration
   - `/composer.json` - PHP dependencies (PHPMailer)

### Modified Files
1. `/config/config.php` - Added email functions and PHPMailer integration
2. `/database.sql` - Updated users table schema
3. `/login.html` - Updated forgot password link

## Installation Steps

### 1. Database Migration

Run the database migration to add password reset fields to the users table:

```bash
mysql -u root -p helpdesk_db < migrations/add_password_reset_fields.sql
```

Or manually execute this SQL:

```sql
USE helpdesk_db;

ALTER TABLE users
ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS reset_token_expires DATETIME DEFAULT NULL,
ADD INDEX IF NOT EXISTS idx_reset_token (reset_token);
```

### 2. Install Dependencies

PHPMailer has already been installed via Composer. If you need to reinstall:

```bash
cd /home/user/helpdesk
composer install
```

### 3. Email Configuration

#### Step 1: Copy the environment file

```bash
cp .env.example .env
```

#### Step 2: Configure SMTP Settings

Edit the `.env` file and add your SMTP credentials:

```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@jpbdselangor.gov.my
SMTP_PASSWORD=your-app-password
SMTP_FROM_EMAIL=noreply@jpbdselangor.gov.my
SMTP_FROM_NAME=PLAN Malaysia Selangor Helpdesk
```

#### Step 3: Gmail Setup (if using Gmail)

1. Enable 2-Factor Authentication in your Google Account
2. Go to: https://myaccount.google.com/apppasswords
3. Generate an App Password
4. Use the App Password as `SMTP_PASSWORD` in your `.env` file

#### Alternative SMTP Providers

**Microsoft 365 / Outlook:**
```env
SMTP_HOST=smtp.office365.com
SMTP_PORT=587
```

**Custom SMTP Server:**
```env
SMTP_HOST=mail.jpbdselangor.gov.my
SMTP_PORT=587
```

### 4. File Permissions

Ensure proper permissions:

```bash
chmod 644 .env
chmod 755 api/forgot-password.php
chmod 755 api/verify-reset-token.php
chmod 755 api/reset-password.php
```

## Configuration Options

### Token Expiry Time

Default: 1 hour

To change the expiry time, edit `/config/config.php`:

```php
define('RESET_TOKEN_EXPIRY_HOURS', 2); // Change to 2 hours
```

### Email Templates

Email templates are defined in `/config/config.php` in the `sendPasswordResetEmail()` function. Customize the HTML and text content as needed.

### Password Requirements

Password strength requirements are defined in `/api/reset-password.php`:

- Minimum 8 characters
- At least 1 uppercase letter
- At least 1 lowercase letter
- At least 1 number

To modify these requirements, edit the validation logic in both:
- `/api/reset-password.php` (backend)
- `/reset-password.html` (frontend)

## User Flow

1. **Request Reset:**
   - User clicks "Lupa kata laluan?" on login page
   - User enters their email address
   - System generates token and sends email

2. **Email Received:**
   - User receives email with reset link
   - Link format: `reset-password.html?token=XXXXX`
   - Link expires in 1 hour

3. **Reset Password:**
   - User clicks link in email
   - System verifies token validity
   - User enters new password
   - System validates password strength
   - Password is updated, token is cleared

4. **Confirmation:**
   - User receives confirmation email
   - User is redirected to login page
   - User can now login with new password

## Security Features

### 1. Token Security
- Tokens are generated using `random_bytes()` (cryptographically secure)
- Tokens are 128 characters long (64 bytes hexadecimal)
- Tokens expire after 1 hour
- Tokens are single-use (cleared after successful reset)

### 2. User Enumeration Prevention
- System returns success message even if email doesn't exist
- Prevents attackers from discovering valid email addresses

### 3. Email Domain Validation
- Only @jpbdselangor.gov.my emails are accepted
- Prevents unauthorized access attempts

### 4. Password Strength Validation
- Frontend and backend validation
- Real-time password strength indicator
- Enforces strong password requirements

### 5. HTTPS Recommendation
- For production, enable HTTPS
- Update session settings in `/config/config.php`:
  ```php
  'secure' => true, // Set to true when using HTTPS
  ```

## Troubleshooting

### Email Not Sending

**Check SMTP credentials:**
```bash
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/php_errors.log
```

**Test SMTP connection:**
Create a test file `test-email.php`:
```php
<?php
require_once 'config/config.php';
$result = sendEmail('test@jpbdselangor.gov.my', 'Test', 'Test message');
var_dump($result);
```

### Token Not Found

**Check database:**
```sql
SELECT id, email, reset_token, reset_token_expires
FROM users
WHERE email = 'user@jpbdselangor.gov.my';
```

### Token Expired

Tokens expire after 1 hour. User must request a new reset link.

### Email Goes to Spam

1. Configure SPF/DKIM records for your domain
2. Use official email server (not Gmail for production)
3. Ensure FROM email matches domain

## Testing

### Manual Testing Checklist

- [ ] User can request password reset
- [ ] Email is received with reset link
- [ ] Reset link opens reset password page
- [ ] Invalid/expired tokens show error message
- [ ] Password strength indicator works
- [ ] Password validation works (frontend & backend)
- [ ] Password reset succeeds
- [ ] Confirmation email is received
- [ ] User can login with new password
- [ ] Old password no longer works
- [ ] Token is cleared after successful reset

### Test Accounts

Use the default test user:
- **Email:** ahmad.user@jpbdselangor.gov.my
- **Password:** user123 (before reset)

## API Documentation

### POST /api/forgot-password.php

**Request:**
```json
{
  "email": "user@jpbdselangor.gov.my"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Arahan reset kata laluan telah dihantar ke emel anda",
  "data": []
}
```

### GET /api/verify-reset-token.php?token=XXX

**Response (Valid Token):**
```json
{
  "success": true,
  "message": "Token sah",
  "data": {
    "email": "user@jpbdselangor.gov.my",
    "nama_penuh": "Ahmad Bin Abdullah"
  }
}
```

### POST /api/reset-password.php

**Request:**
```json
{
  "token": "token-string",
  "password": "NewPassword123",
  "confirm_password": "NewPassword123"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Kata laluan anda telah berjaya dikemaskini",
  "data": []
}
```

## Production Deployment

### Pre-deployment Checklist

- [ ] Configure production SMTP credentials
- [ ] Enable HTTPS and update session settings
- [ ] Set `display_errors = 0` in `config.php`
- [ ] Configure proper error logging
- [ ] Test email delivery in production
- [ ] Set up monitoring for failed emails
- [ ] Configure firewall to allow SMTP connections
- [ ] Review and update email templates with production URLs

### Environment Variables

For production, consider using actual environment variables instead of `.env` file:

```bash
export SMTP_HOST="smtp.jpbdselangor.gov.my"
export SMTP_PORT="587"
export SMTP_USERNAME="helpdesk@jpbdselangor.gov.my"
export SMTP_PASSWORD="secure-password"
```

## Support

For issues or questions:
- **IT Support:** 03-5511 8888
- **Email:** helpdesk@jpbdselangor.gov.my

## License

© 2024 PLAN Malaysia Selangor. All Rights Reserved.
