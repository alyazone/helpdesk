-- Migration: Add Multi-Role Support
-- Description: Allows users to have multiple roles assigned simultaneously
-- Date: 2026-01-09

-- Create user_roles table for many-to-many relationship between users and roles
CREATE TABLE IF NOT EXISTS user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_name VARCHAR(100) NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_role (user_id, role_name),
    INDEX idx_user_id (user_id),
    INDEX idx_role_name (role_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add unit_korporat role to users table ENUM
ALTER TABLE users MODIFY COLUMN role ENUM(
    'user',
    'admin',
    'unit_aduan_dalaman',
    'unit_aset',
    'bahagian_pentadbiran_kewangan',
    'unit_it_sokongan',
    'unit_korporat',
    'unit_pentadbiran',
    'staff'
) DEFAULT 'user';

-- Insert roles for the specified users based on requirements

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

-- Create table for unit_korporat staff (similar to other unit tables)
CREATE TABLE IF NOT EXISTS unit_korporat_officers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    jawatan VARCHAR(255),
    bahagian VARCHAR(255),
    status ENUM('aktif', 'tidak_aktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for unit_pentadbiran staff
CREATE TABLE IF NOT EXISTS unit_pentadbiran_officers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    jawatan VARCHAR(255),
    bahagian VARCHAR(255),
    status ENUM('aktif', 'tidak_aktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add sample officers to unit_korporat_officers table
INSERT INTO unit_korporat_officers (nama, email, jawatan, bahagian, status)
VALUES ('Muhammad Adzhan bin Mohd Saike', 'adzhan@jpbdselangor.gov.my', 'Pegawai Unit Korporat', 'Unit Korporat (Laporan)', 'aktif')
ON DUPLICATE KEY UPDATE nama = nama;

-- Migration complete
-- Users now support multiple roles through the user_roles table
-- All specified users have been assigned their respective roles
