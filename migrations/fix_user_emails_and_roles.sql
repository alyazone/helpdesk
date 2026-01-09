-- Migration: Update User Emails and Create Missing Users
-- Description: Updates existing emails to match specified format and creates missing users
-- Date: 2026-01-09

-- Update existing emails to match the specified format
UPDATE users SET email = 'azri@jpbdselangor.gov.my' WHERE email = 'azri.hanis@jpbdselangor.gov.my';
UPDATE users SET email = 'alia@jpbdselangor.gov.my' WHERE email = 'alia.yusof@jpbdselangor.gov.my';

-- Create missing user: Puan Siti Norhayati binti Mokti
-- Password: admin123 (hashed with bcrypt cost 12)
INSERT INTO users (nama_penuh, email, password, jawatan, bahagian, unit, role, status)
VALUES (
    'Puan Siti Norhayati binti Mokti',
    'norhayati@jpbdselangor.gov.my',
    '$2y$12$8Wos13KhnYKoWOttGDI28OSaxxrS/oAIhtcczMKtMY5rm0EytQF7i',
    'Pentadbir Sistem',
    'Unit Aduan Dalaman',
    'Unit Aduan Dalaman',
    'admin',
    'active'
)
ON DUPLICATE KEY UPDATE email = email;

-- Create missing user: Muhammad Adzhan bin Mohd Saike
INSERT INTO users (nama_penuh, email, password, jawatan, bahagian, unit, role, status)
VALUES (
    'Muhammad Adzhan bin Mohd Saike',
    'adzhan@jpbdselangor.gov.my',
    '$2y$12$8Wos13KhnYKoWOttGDI28OSaxxrS/oAIhtcczMKtMY5rm0EytQF7i',
    'Pegawai Unit Korporat',
    'Unit Korporat (Laporan)',
    'Unit Korporat',
    'unit_korporat',
    'active'
)
ON DUPLICATE KEY UPDATE email = email;

-- Now re-assign all roles with updated emails

-- Clear existing role assignments for these users (to start fresh)
DELETE FROM user_roles WHERE user_id IN (
    SELECT id FROM users WHERE email IN (
        'norhayati@jpbdselangor.gov.my',
        'alia@jpbdselangor.gov.my',
        'azri@jpbdselangor.gov.my',
        'maznah@jpbdselangor.gov.my',
        'adzhan@jpbdselangor.gov.my'
    )
);

-- 1. Puan Siti Norhayati binti Mokti (norhayati@jpbdselangor.gov.my)
-- Roles: Super Admin, Unit Aduan Dalaman, Unit ICT (Pelaksana), Pengguna Biasa
INSERT INTO user_roles (user_id, role_name)
SELECT id, 'admin' FROM users WHERE email = 'norhayati@jpbdselangor.gov.my'
ON DUPLICATE KEY UPDATE role_name = role_name;

INSERT INTO user_roles (user_id, role_name)
SELECT id, 'unit_aduan_dalaman' FROM users WHERE email = 'norhayati@jpbdselangor.gov.my'
ON DUPLICATE KEY UPDATE role_name = role_name;

INSERT INTO user_roles (user_id, role_name)
SELECT id, 'unit_it_sokongan' FROM users WHERE email = 'norhayati@jpbdselangor.gov.my'
ON DUPLICATE KEY UPDATE role_name = role_name;

INSERT INTO user_roles (user_id, role_name)
SELECT id, 'user' FROM users WHERE email = 'norhayati@jpbdselangor.gov.my'
ON DUPLICATE KEY UPDATE role_name = role_name;

-- 2. Alia binti Mohd Yusof (alia@jpbdselangor.gov.my)
-- Roles: Pegawai Pelulus, Pengguna Biasa
INSERT INTO user_roles (user_id, role_name)
SELECT id, 'bahagian_pentadbiran_kewangan' FROM users WHERE email = 'alia@jpbdselangor.gov.my'
ON DUPLICATE KEY UPDATE role_name = role_name;

INSERT INTO user_roles (user_id, role_name)
SELECT id, 'user' FROM users WHERE email = 'alia@jpbdselangor.gov.my'
ON DUPLICATE KEY UPDATE role_name = role_name;

-- 3. Azri Hanis bin Zul (azri@jpbdselangor.gov.my)
-- Roles: Unit Aset, Unit Pentadbiran, Pengguna Biasa
INSERT INTO user_roles (user_id, role_name)
SELECT id, 'unit_aset' FROM users WHERE email = 'azri@jpbdselangor.gov.my'
ON DUPLICATE KEY UPDATE role_name = role_name;

INSERT INTO user_roles (user_id, role_name)
SELECT id, 'unit_pentadbiran' FROM users WHERE email = 'azri@jpbdselangor.gov.my'
ON DUPLICATE KEY UPDATE role_name = role_name;

INSERT INTO user_roles (user_id, role_name)
SELECT id, 'user' FROM users WHERE email = 'azri@jpbdselangor.gov.my'
ON DUPLICATE KEY UPDATE role_name = role_name;

-- 4. Maznah binti Marzuki (maznah@jpbdselangor.gov.my)
-- Roles: Unit Aset, Unit Pentadbiran, Pengguna Biasa
INSERT INTO user_roles (user_id, role_name)
SELECT id, 'unit_aset' FROM users WHERE email = 'maznah@jpbdselangor.gov.my'
ON DUPLICATE KEY UPDATE role_name = role_name;

INSERT INTO user_roles (user_id, role_name)
SELECT id, 'unit_pentadbiran' FROM users WHERE email = 'maznah@jpbdselangor.gov.my'
ON DUPLICATE KEY UPDATE role_name = role_name;

INSERT INTO user_roles (user_id, role_name)
SELECT id, 'user' FROM users WHERE email = 'maznah@jpbdselangor.gov.my'
ON DUPLICATE KEY UPDATE role_name = role_name;

-- 5. Muhammad Adzhan bin Mohd Saike (adzhan@jpbdselangor.gov.my)
-- Roles: Unit Korporat, Pengguna Biasa
INSERT INTO user_roles (user_id, role_name)
SELECT id, 'unit_korporat' FROM users WHERE email = 'adzhan@jpbdselangor.gov.my'
ON DUPLICATE KEY UPDATE role_name = role_name;

INSERT INTO user_roles (user_id, role_name)
SELECT id, 'user' FROM users WHERE email = 'adzhan@jpbdselangor.gov.my'
ON DUPLICATE KEY UPDATE role_name = role_name;

-- Migration complete
