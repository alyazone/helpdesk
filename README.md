# PLAN Malaysia Selangor Helpdesk System

A comprehensive complaint and suggestion management platform designed for PLAN Malaysia Selangor organization, featuring multi-unit workflow processing, asset damage reporting, and administrative oversight.

## Overview

The PLAN Malaysia Selangor Helpdesk System is a web-based application that enables internal staff to submit complaints and suggestions, track their progress through a multi-level approval workflow, and provide feedback upon completion. The system implements a structured three-unit workflow specifically designed for government organizational processes.

## Features

### User Features
- **Complaint Submission** - Submit complaints (aduan) or suggestions (cadangan) with detailed information
- **Asset Damage Reporting** - Specialized form for reporting asset damage with asset details and condition assessment
- **File Attachments** - Upload supporting documents (JPG, PNG, PDF, DOC, DOCX up to 5MB)
- **Status Tracking** - Check complaint status and progress by email
- **Feedback System** - Rate completed complaints (cemerlang, baik, memuaskan, tidak_memuaskan)
- **Password Reset** - Self-service password reset via email with token validation

### Multi-Unit Workflow System

The system implements a **3-unit workflow** for complaint processing:

1. **Unit Aduan Dalaman (Internal Complaints Unit)**
   - Receives and verifies all new complaints
   - Generates official "Dokumen Unit Aduan" documentation
   - Forwards verified complaints to appropriate Unit Aset officers

2. **Unit Aset (Asset Unit)**
   - Reviews asset damage complaints
   - Completes "Borang Aduan Kerosakan Aset Alih" (Asset Damage Form)
   - Estimates maintenance costs and provides recommendations
   - Forwards to approval officers

3. **Bahagian Pentadbiran & Kewangan (Administration & Finance Department)**
   - Acts as approval authority
   - Reviews and approves/rejects complaints
   - Generates final documentation with all sections
   - Provides final decision and authorization

### Administrative Features
- **Dashboard** - Statistics, recent complaints, and system overview
- **Complaint Management** - View, edit, and manage all complaints
- **User Management** - Create, update, activate/deactivate user accounts
- **Officer Management** - Manage responding officers and assignments
- **Reports & Analytics** - Complaint statistics, completion rates, feedback analysis
- **Export Functionality** - CSV export for complaints and feedback data

## Technology Stack

| Component | Technology |
|-----------|-----------|
| **Backend** | PHP 8.2+ |
| **Web Server** | Apache |
| **Database** | MySQL 8.0 |
| **Frontend** | HTML5, JavaScript (Vanilla), Tailwind CSS |
| **Email** | PHPMailer 6.9+ (SMTP) |
| **Database Access** | PDO (PHP Data Objects) |
| **Session Management** | PHP Sessions with HttpOnly cookies |
| **Containerization** | Docker & Docker Compose |
| **UI Framework** | Tailwind CSS, Font Awesome 6.4.0 |
| **Dependency Manager** | Composer |

## User Roles & Permissions

| Role | Permissions | Responsibilities |
|------|------------|------------------|
| **user** | Submit complaints, check status, provide feedback | Regular staff member |
| **admin** | Full system access, user management, reporting | System administrator |
| **unit_aduan_dalaman** | Verify complaints, generate documents | Internal Complaints Unit staff |
| **unit_aset** | Review asset damage, fill forms | Asset Unit staff |
| **bahagian_pentadbiran_kewangan** | Approve/reject complaints, final authority | Finance & Administration staff |
| **unit_it_sokongan** | Handle IT support tickets | IT Support staff |

## Installation

### Prerequisites

- Docker and Docker Compose
- Git
- Port 8080 available (or modify docker-compose.yml)

### Quick Start with Docker

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd helpdesk
   ```

2. **Configure environment**
   ```bash
   cp .env.example .env
   ```
   Edit `.env` and configure:
   - Database credentials
   - SMTP settings for email
   - Application URL

3. **Start Docker containers**
   ```bash
   docker-compose up -d
   ```

4. **Import database schema**
   ```bash
   docker exec -i helpdesk-db mysql -u root -p<password> helpdesk_db < helpdesk_db.sql
   ```

5. **Run migrations** (if needed)
   ```bash
   docker exec -i helpdesk-db mysql -u root -p<password> helpdesk_db < migrations/database.sql
   docker exec -i helpdesk-db mysql -u root -p<password> helpdesk_db < migrations/add_multi_unit_workflow.sql
   docker exec -i helpdesk-db mysql -u root -p<password> helpdesk_db < migrations/add_password_reset_fields.sql
   ```

6. **Access the application**
   - Application: http://localhost:8080
   - phpMyAdmin: http://localhost:8081

### Manual Installation

1. **System Requirements**
   - PHP >= 7.4 (PHP 8.2+ recommended)
   - MySQL 8.0+
   - Apache with mod_rewrite enabled
   - Composer

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure database**
   - Create a MySQL database named `helpdesk_db`
   - Import `helpdesk_db.sql`
   - Update database credentials in `config/config.php`

4. **Configure SMTP**
   - Edit `config/config.php` with your SMTP settings

5. **Set permissions**
   ```bash
   chmod 755 admin/
   chmod 755 api/
   ```

## Configuration

### Database Configuration

Edit `/config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'helpdesk_db');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```

### Email Configuration

Configure SMTP settings in `/config/config.php`:

```php
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your@email.com');
define('SMTP_PASSWORD', 'your_password');
define('SMTP_FROM_EMAIL', 'noreply@jpbdselangor.gov.my');
define('SMTP_FROM_NAME', 'PLAN Malaysia Selangor Helpdesk');
```

### Email Domain Restriction

By default, only `@jpbdselangor.gov.my` email addresses are allowed for registration. To modify this, edit the validation in:
- `/api/register.php`
- Registration form validation

## Project Structure

```
helpdesk/
├── admin/                          # Admin dashboard & unit interfaces
│   ├── index.php                  # Main admin dashboard
│   ├── complaints.php             # Complaint management
│   ├── users.php                  # User management
│   ├── officers.php               # Officer management
│   ├── reports.php                # Reports & analytics
│   ├── unit-aduan-dalaman/        # Internal Complaints Unit panel
│   ├── unit-aset/                 # Asset Unit panel
│   ├── unit-it-sokongan/          # IT Support Unit panel
│   └── bahagian-pentadbiran-kewangan/  # Finance approval panel
│
├── api/                           # REST API endpoints
│   ├── login.php                  # Authentication
│   ├── register.php               # User registration
│   ├── submit_complaint.php       # Submit complaints
│   ├── check_status.php           # Check complaint status
│   ├── submit_rating.php          # Submit feedback/ratings
│   ├── forgot-password.php        # Password reset request
│   └── admin/                     # Admin API endpoints
│
├── config/
│   └── config.php                 # Database config & helper functions
│
├── migrations/                    # Database migration scripts
├── composer.json                  # PHP dependencies
├── docker-compose.yml             # Docker configuration
├── login.html                     # Login page
├── register.html                  # Registration page
├── borang_aduan.html             # Complaint form
├── semakan.html                  # Status check page
└── helpdesk_db.sql               # Database dump
```

## Usage

### Default Accounts

| Role | Email | Password | Access Level |
|------|-------|----------|--------------|
| Super Admin | admin@jpbdselangor.gov.my | admin123 | Full system access |

**⚠️ IMPORTANT:** Change default passwords immediately after installation!

### User Registration

1. Navigate to `/register.html`
2. Fill in personal details
3. Use valid `@jpbdselangor.gov.my` email address
4. Password must be at least 8 characters with uppercase, lowercase, and numbers
5. Admin approval may be required for activation

### Submitting a Complaint

1. Log in to the system
2. Navigate to complaint form (`/borang_aduan.html`)
3. Select complaint type (aduan or cadangan)
4. Fill in required details:
   - Title and description
   - Priority level
   - Department information
   - Asset details (if asset damage)
5. Attach supporting documents (optional)
6. Submit complaint

### Checking Complaint Status

1. Navigate to `/semakan.html`
2. Enter your registered email address
3. View all your complaints with current status and progress

### Workflow Statuses

| Status | Description |
|--------|-------------|
| `baru` | New complaint just submitted |
| `disahkan_unit_aduan` | Verified by Internal Complaints Unit |
| `dimajukan_unit_aset` | Forwarded to Asset Unit |
| `dalam_semakan_unit_aset` | Under Asset Unit review |
| `dimajukan_pegawai_pelulus` | Forwarded to Approval Officer |
| `diluluskan` | Approved |
| `ditolak` | Rejected |
| `selesai` | Completed |

## Security Features

- **Email Domain Validation** - Only @jpbdselangor.gov.my addresses allowed
- **Password Requirements** - 8+ characters with uppercase, lowercase, and numbers
- **Secure Password Hashing** - bcrypt (PASSWORD_DEFAULT)
- **Token-Based Password Reset** - One-time use tokens with 1-hour expiry
- **Session Security** - HttpOnly cookies and secure session handling
- **Role-Based Access Control** - Strict permission checking for all operations
- **Input Sanitization** - SQL injection and XSS prevention
- **File Upload Validation** - Type and size restrictions on uploads
- **CSRF Protection** - Session-based CSRF prevention

## Database Schema

### Key Tables

- **users** - User accounts with roles and authentication
- **complaints** - Main complaint records
- **attachments** - File uploads linked to complaints
- **borang_kerosakan_aset** - Asset damage complaint forms
- **dokumen_unit_aduan** - Official verification documents
- **officers** - Assigned handling officers
- **complaint_status_history** - Audit trail of status changes
- **notifications** - System notifications
- **workflow_actions** - Log of all workflow actions

For detailed schema, refer to `/helpdesk_db.sql` or `/migrations/database.sql`

## API Endpoints

### Authentication
- `POST /api/login.php` - User login
- `POST /api/register.php` - User registration
- `POST /api/forgot-password.php` - Request password reset
- `POST /api/reset-password.php` - Reset password with token

### Complaints
- `POST /api/submit_complaint.php` - Submit new complaint
- `POST /api/check_status.php` - Check complaint status by email
- `GET /api/get_complaint_details.php` - Get complaint details
- `POST /api/submit_rating.php` - Submit feedback/rating

### Admin
- `POST /api/admin/manage_user.php` - User management (create/update/delete)
- `POST /api/admin/manage_officer.php` - Officer management
- `POST /api/admin/delete_complaint.php` - Delete complaint

## Troubleshooting

### Common Issues

**Database Connection Failed**
- Verify database credentials in `config/config.php`
- Ensure MySQL service is running
- Check if database `helpdesk_db` exists

**Email Not Sending**
- Verify SMTP credentials in `config/config.php`
- Check SMTP port (587 for TLS, 465 for SSL)
- Ensure firewall allows outbound SMTP connections
- Check PHP `mail()` function or PHPMailer configuration

**File Upload Failed**
- Check directory permissions for upload folders
- Verify `upload_max_filesize` in `php.ini`
- Ensure file type is allowed (JPG, PNG, PDF, DOC, DOCX)
- Check file size limit (5MB default)

**Login Issues**
- Verify user account is active (`status = 'active'` in database)
- Ensure email domain is `@jpbdselangor.gov.my`
- Check session configuration in `config/config.php`

## Documentation

- [Multi-Unit Workflow Guide](MULTI_UNIT_WORKFLOW_README.md) - Detailed workflow documentation
- [Forgot Password Setup](FORGOT_PASSWORD_SETUP.md) - Password reset configuration

## Development

### Running in Development Mode

```bash
docker-compose up
```

Application will be available at http://localhost:8080 with live file changes.

### Database Migrations

New migrations should be placed in `/migrations/` directory and applied in order:

```bash
docker exec -i helpdesk-db mysql -u root -p<password> helpdesk_db < migrations/your_migration.sql
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is proprietary software developed for PLAN Malaysia Selangor.

## Support

For technical support or questions:
- Contact: IT Department, PLAN Malaysia Selangor
- Email: admin@jpbdselangor.gov.my

## Acknowledgments

- PLAN Malaysia Selangor for project requirements and specifications
- PHPMailer for email functionality
- Tailwind CSS for UI framework
