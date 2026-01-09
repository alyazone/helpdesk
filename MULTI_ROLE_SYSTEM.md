# Multi-Role System Implementation

## Overview

This document describes the multi-role system implemented for the PLAN Malaysia Selangor Helpdesk System. The system allows users to have multiple roles assigned simultaneously, enabling them to switch between different access levels and functionalities.

## Implementation Date
- **Date:** January 9, 2026
- **Migration File:** `migrations/add_multi_role_support.sql`

## Key Features

### 1. Multiple Roles per User
- Users can have multiple roles assigned simultaneously
- Roles are stored in a separate `user_roles` table (many-to-many relationship)
- Users can switch between their assigned roles dynamically

### 2. Role Switching
- Enhanced role-switcher component shows all available roles
- Users can switch between roles without re-logging in
- Each role redirects to its appropriate dashboard/interface

### 3. New Roles Added
- **Unit Korporat (Laporan):** View-only access to all complaints with reporting capabilities
- **Unit Pentadbiran (Pelaksana):** Access to IT Support interfaces

## Database Schema

### New Table: `user_roles`

```sql
CREATE TABLE user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_name VARCHAR(100) NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_role (user_id, role_name)
);
```

### Updated `users` Table
- Added new roles to the `role` ENUM: `unit_korporat`, `unit_pentadbiran`

## User Role Assignments

### 1. Puan Siti Norhayati binti Mokti (norhayati@jpbdselangor.gov.my)
**Roles:**
- `admin` - Super Admin (full system access)
- `unit_aduan_dalaman` - Unit Aduan Dalaman (complaint verification)
- `unit_it_sokongan` - Unit ICT/Pelaksana (IT support tasks)
- `user` - Pengguna Biasa (regular user access)

**Access:**
- `/admin/` - Super Admin dashboard
- `/admin/unit-aduan-dalaman/` - Complaint verification interface
- `/admin/unit-it-sokongan/` - IT support interface
- Regular user functionalities

### 2. Alia binti Mohd Yusof (alia@jpbdselangor.gov.my)
**Roles:**
- `bahagian_pentadbiran_kewangan` - Pegawai Pelulus (approval authority)
- `user` - Pengguna Biasa (regular user access)

**Access:**
- `/admin/bahagian-pentadbiran-kewangan/` - Approval interface
- Regular user functionalities

### 3. Azri Hanis bin Zul (azri@jpbdselangor.gov.my)
**Roles:**
- `unit_aset` - Unit Aset (asset review)
- `unit_pentadbiran` - Unit Pentadbiran (IT support tasks)
- `user` - Pengguna Biasa (regular user access)

**Access:**
- `/admin/unit-aset/` - Asset review interface
- `/admin/unit-it-sokongan/` - IT support interface (via Unit Pentadbiran role)
- Regular user functionalities

### 4. Maznah binti Marzuki (maznah@jpbdselangor.gov.my)
**Roles:**
- `unit_aset` - Unit Aset (asset review)
- `unit_pentadbiran` - Unit Pentadbiran (IT support tasks)
- `user` - Pengguna Biasa (regular user access)

**Access:**
- `/admin/unit-aset/` - Asset review interface
- `/admin/unit-it-sokongan/` - IT support interface (via Unit Pentadbiran role)
- Regular user functionalities

### 5. Muhammad Adzhan bin Mohd Saike (adzhan@jpbdselangor.gov.my)
**Roles:**
- `unit_korporat` - Unit Korporat (reporting and analytics)
- `user` - Pengguna Biasa (regular user access)

**Access:**
- `/admin/unit-korporat/` - Reports and analytics dashboard (view-only)
- Regular user functionalities

## Architecture

### Configuration Functions (config/config.php)

#### New Helper Functions:
```php
// Get all roles assigned to a user
getUserRoles($userId)

// Check if user has a specific role
hasRole($userId, $roleName)

// Check if user has any of the specified roles
hasAnyRole($userId, $roleNames)

// Check if active role has access to a specific interface
canAccessInterface($interface)

// Get all available roles for role switching
getAvailableRoles($userId)

// Check if user is Unit Korporat
isUnitKorporat()

// Check if user is Unit Pentadbiran
isUnitPentadbiran()
```

### API Endpoints

#### 1. `/api/admin/manage_user_roles.php`
Manage user role assignments (Admin only)

**Methods:**
- `GET` - Retrieve roles for a user
- `POST` - Assign multiple roles to a user
- `DELETE` - Remove a specific role from a user

**Example Usage:**
```javascript
// Get user roles
fetch('/helpdesk/api/admin/manage_user_roles.php?user_id=5')
  .then(response => response.json())
  .then(data => console.log(data));

// Assign roles
fetch('/helpdesk/api/admin/manage_user_roles.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    user_id: 5,
    roles: ['admin', 'unit_aduan_dalaman', 'user']
  })
});
```

#### 2. `/api/switch_role.php`
Switch between assigned roles

**Method:** `POST`

**Example Usage:**
```javascript
fetch('/helpdesk/api/switch_role.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ role: 'unit_korporat' })
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    window.location.href = '/helpdesk/admin/unit-korporat/index.php';
  }
});
```

#### 3. `/api/check_session.php`
Updated to return all user roles

**Response:**
```json
{
  "success": true,
  "message": "User is logged in",
  "data": {
    "logged_in": true,
    "user": {
      "id": 5,
      "nama": "Muhammad Adzhan bin Mohd Saike",
      "email": "adzhan@jpbdselangor.gov.my",
      "role": "unit_korporat",
      "active_role": "unit_korporat",
      "roles": ["unit_korporat", "user"]
    }
  }
}
```

### Frontend Components

#### Role Switcher (assets/js/role-switcher.js)
Enhanced to display all assigned roles dynamically

**Features:**
- Displays all assigned roles as buttons
- Shows active role with visual indicator
- Color-coded by role type
- Automatic redirect after role switch
- Fixed position at bottom-left corner

**Role Icons:**
- 👤 User (Pengguna Biasa)
- 👑 Admin (Super Admin)
- 📝 Unit Aduan Dalaman
- 📦 Unit Aset
- ✅ Pegawai Pelulus
- 💻 Unit ICT (Pelaksana)
- 📊 Unit Korporat (Laporan)
- ⚙️ Unit Pentadbiran (Pelaksana)

## Unit Korporat Interface

### Overview
Unit Korporat has **view-only** access to all complaints with reporting capabilities.

### Files Created:
- `/admin/unit-korporat/index.php` - Dashboard with statistics
- `/admin/unit-korporat/complaints.php` - List all complaints with filters
- `/admin/unit-korporat/view_complaint.php` - View complaint details (read-only)
- `/admin/unit-korporat/reports.php` - Generate various reports

### Features:
- ✅ View all complaints and their complete details
- ✅ View workflow history for each complaint
- ✅ Access to reporting and analytics
- ✅ Filter and search capabilities
- ❌ Cannot edit or process complaints
- ❌ Cannot approve or reject complaints
- ❌ Cannot assign tasks

### Report Types Available:
1. **Monthly Summary Report** - Statistics by month
2. **Category Report** - Analysis by complaint category
3. **Status Report** - Breakdown by workflow status
4. **Priority Report** - Analysis by priority level
5. **Department Report** - Summary by department/unit
6. **Custom Export** - CSV/Excel/PDF with custom filters

## Migration Instructions

### 1. Run the Migration

**Option A: Via PHP Script (Recommended)**
1. Log in as admin
2. Visit: `/helpdesk/api/admin/run_migration.php`
3. The script will execute the migration and show results
4. Verify that all 5 users have been assigned their roles

**Option B: Via MySQL Command Line**
```bash
mysql -u appuser -p helpdesk_db < migrations/add_multi_role_support.sql
```

### 2. Verify Installation

**Check User Roles:**
```sql
SELECT u.nama_penuh, u.email, GROUP_CONCAT(ur.role_name) as roles
FROM users u
LEFT JOIN user_roles ur ON u.id = ur.user_id
WHERE u.email IN (
  'norhayati@jpbdselangor.gov.my',
  'alia@jpbdselangor.gov.my',
  'azri@jpbdselangor.gov.my',
  'maznah@jpbdselangor.gov.my',
  'adzhan@jpbdselangor.gov.my'
)
GROUP BY u.id;
```

### 3. Test Role Switching

1. Log in as one of the configured users
2. Look for the role-switcher button at bottom-left corner
3. Click to expand and see all assigned roles
4. Click on any role to switch
5. Verify you are redirected to the appropriate dashboard

## Security Considerations

### Role Validation
- All role switches are validated against the user's assigned roles
- Users can only access interfaces for their assigned roles
- The `active_role` in session determines current access level

### Authorization Guards
- Each protected page checks `isLoggedIn()` and specific role function
- Admin-only endpoints verify `isAdmin()` before allowing access
- Role management API restricted to Super Admins only

### Audit Trail
- All role switches are logged in server error logs
- `user_roles` table tracks who assigned each role and when
- Migration includes `assigned_by` and `assigned_at` fields

## Troubleshooting

### Issue: Role switcher not appearing
**Solution:**
- Check that user has roles in `user_roles` table
- Verify `role-switcher.js` is loaded on the page
- Check browser console for JavaScript errors

### Issue: Cannot switch to a role
**Solution:**
- Verify role is assigned in `user_roles` table
- Check that role name matches exactly (case-sensitive)
- Ensure user is logged in with valid session

### Issue: Access denied after role switch
**Solution:**
- Verify the interface has proper authorization checks
- Check that `canAccessInterface()` includes the interface in mapping
- Ensure `$_SESSION['active_role']` is set correctly

### Issue: Migration fails with duplicate entry
**Solution:**
- This is normal for re-running migration
- The `ON DUPLICATE KEY UPDATE` clause handles existing roles
- Check that `user_roles` table exists and has correct schema

## Future Enhancements

### Potential Improvements:
1. **Role Permissions Matrix** - Fine-grained permissions per role
2. **Temporary Role Assignments** - Time-limited role access
3. **Role Request System** - Users can request additional roles
4. **Enhanced Audit Logs** - Track all actions per role
5. **Role-based Notifications** - Different notifications per role
6. **Dashboard Customization** - Personalized dashboards per role

## Support

For issues or questions about the multi-role system:
1. Check this documentation first
2. Review server error logs for detailed error messages
3. Verify database schema matches migration file
4. Contact system administrator for role assignment changes

---

**Document Version:** 1.0
**Last Updated:** 2026-01-09
**Author:** System Administrator
