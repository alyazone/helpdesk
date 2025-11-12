# PLAN Malaysia Selangor - Sistem Helpdesk

Sistem Helpdesk untuk Jabatan Perancangan Bandar dan Desa Negeri Selangor dengan backend PHP (tanpa framework Laravel).

## 🚀 Features

- **User Authentication**: Login, registration, and session management
- **Complaint Submission**: Submit complaints/suggestions with file attachments
- **Status Tracking**: Check complaint status and progress
- **Admin Dashboard**: Manage complaints and update status
- **File Upload**: Support for images, PDF, and documents (max 5MB)
- **Rating System**: Users can rate completed complaints
- **Responsive Design**: Built with Tailwind CSS for all devices

## 📋 Prerequisites

- **PHP**: Version 7.4 or higher
- **MySQL**: Version 5.7 or higher / MariaDB
- **Web Server**: Apache or Nginx
- **PHP Extensions**: PDO, PDO_MySQL

## 🛠️ Installation

### 1. Clone or Download the Repository

```bash
git clone https://github.com/your-repo/helpdesk.git
cd helpdesk
```

### 2. Database Setup

1. Create a MySQL database:

```sql
CREATE DATABASE helpdesk_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the database schema:

```bash
mysql -u root -p helpdesk_db < database.sql
```

Or manually import using phpMyAdmin:
- Open phpMyAdmin
- Select `helpdesk_db` database
- Click "Import"
- Choose `database.sql` file
- Click "Go"

### 3. Configure Database Connection

Edit `config/config.php` and update the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'helpdesk_db');
define('DB_USER', 'your_username');      // Change this
define('DB_PASS', 'your_password');      // Change this
```

### 4. Set File Permissions

Ensure the uploads directory is writable:

```bash
mkdir -p uploads
chmod 755 uploads
```

For Linux/Mac:
```bash
sudo chown -R www-data:www-data uploads
```

For Windows (XAMPP/WAMP):
- Right-click `uploads` folder
- Properties → Security → Edit
- Give "Full Control" to your user

### 5. Configure Web Server

#### Apache (.htaccess)

Create `.htaccess` in the root directory:

```apache
RewriteEngine On
RewriteBase /helpdesk/

# Redirect to HTTPS (optional)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Prevent directory listing
Options -Indexes

# Protect config files
<FilesMatch "^(config\.php|database\.sql)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

#### Nginx

Add to your nginx configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/helpdesk;
    index index.html index.php;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\. {
        deny all;
    }
}
```

### 6. Start Your Web Server

#### XAMPP (Windows/Mac/Linux):
1. Start Apache and MySQL from XAMPP Control Panel
2. Access: `http://localhost/helpdesk/`

#### WAMP (Windows):
1. Start WAMP
2. Access: `http://localhost/helpdesk/`

#### Built-in PHP Server (Development Only):
```bash
php -S localhost:8000
```
Access: `http://localhost:8000/`

## 👤 Default Login Credentials

### Admin Account
- **Email**: admin@jpbdselangor.gov.my
- **Password**: admin123

### Test User Account
- **Email**: ahmad.user@jpbdselangor.gov.my
- **Password**: user123

**⚠️ IMPORTANT**: Change these passwords immediately after first login!

## 📁 Project Structure

```
helpdesk/
├── api/                          # Backend API files
│   ├── login.php                # User login
│   ├── register.php             # User registration
│   ├── logout.php               # User logout
│   ├── submit_complaint.php     # Submit complaint
│   ├── check_status.php         # Check complaint status
│   ├── submit_rating.php        # Submit complaint rating
│   ├── upload_handler.php       # File upload handler
│   ├── get_complaint_details.php # Get complaint details
│   └── update_complaint_status.php # Update status (admin)
│
├── admin/                       # Admin panel
│   ├── index.php               # Dashboard
│   └── complaints.php          # All complaints list
│
├── config/                      # Configuration files
│   └── config.php              # Database & app config
│
├── uploads/                     # Uploaded files directory
│
├── index.html                   # Old complaint form (legacy)
├── v1.html                     # Main complaint form
├── login.html                  # Login page
├── semakan.html                # Status checking page
├── script.js                   # Frontend JavaScript (legacy)
├── styles.css                  # Custom CSS (legacy)
├── logoheader1.png             # Header logo
├── database.sql                # Database schema
└── README.md                   # This file
```

## 🔧 Configuration Options

Edit `config/config.php` to customize:

```php
// Upload settings
define('MAX_FILE_SIZE', 5242880);  // 5MB in bytes

// Allowed file types
define('ALLOWED_FILE_TYPES', [
    'image/jpeg',
    'image/png',
    'application/pdf',
    // Add more types as needed
]);

// Email domain restriction
define('ALLOWED_EMAIL_DOMAIN', 'jpbdselangor.gov.my');

// Application URL
define('APP_URL', 'http://localhost/helpdesk');
```

## 📖 Usage Guide

### For Users

1. **Submit Complaint**:
   - Visit `v1.html`
   - Fill in the complaint form
   - Upload attachments (optional)
   - Submit

2. **Check Status**:
   - Visit `semakan.html`
   - Enter your email address
   - View all your complaints and their status

3. **Rate Completed Complaints**:
   - Check your complaint status
   - For completed complaints, click rating buttons
   - Choose: Cemerlang, Baik, Memuaskan, or Tidak Memuaskan

### For Admins

1. **Login**:
   - Visit `login.html`
   - Use admin credentials
   - Redirected to admin dashboard

2. **View Dashboard**:
   - Statistics overview
   - Recent complaints
   - Quick actions

3. **Manage Complaints**:
   - View all complaints
   - Filter by status
   - Search complaints
   - Update status and progress

## 🔐 Security Considerations

1. **Change Default Passwords**: Update admin and test user passwords immediately
2. **Use HTTPS**: Enable SSL/TLS for production
3. **Database Security**: Use strong database passwords
4. **File Upload**: Only allowed file types can be uploaded
5. **Email Domain**: Only `@jpbdselangor.gov.my` emails are allowed
6. **SQL Injection**: All queries use prepared statements
7. **XSS Protection**: Input sanitization implemented

## 🐛 Troubleshooting

### Database Connection Error
- Check `config/config.php` credentials
- Verify MySQL service is running
- Ensure database exists

### File Upload Fails
- Check `uploads/` directory permissions
- Verify `MAX_FILE_SIZE` in config
- Check PHP `upload_max_filesize` and `post_max_size` in `php.ini`

### API Returns 404
- Ensure `mod_rewrite` is enabled (Apache)
- Check `.htaccess` file exists
- Verify file paths are correct

### Session Issues
- Ensure PHP session is enabled
- Check session storage permissions
- Clear browser cookies

## 📞 Support

For issues or questions:
- **Email**: helpdesk@jpbdselangor.gov.my
- **Phone**: 03-5511 8888
- **Department**: Bahagian IT, PLAN Malaysia Selangor

## 📝 License

© 2025 PLAN Malaysia Selangor - Jabatan Perancangan Bandar dan Desa Negeri Selangor. All Rights Reserved.

## 🔄 Version History

### Version 1.0.0 (2025-01-15)
- Initial release
- User authentication system
- Complaint submission
- Status tracking
- Admin dashboard
- File upload support
- Rating system

---

**Developed for**: Jabatan Perancangan Bandar dan Desa Negeri Selangor
**Framework**: Pure PHP (No Laravel)
**Frontend**: Tailwind CSS
**Database**: MySQL/MariaDB
