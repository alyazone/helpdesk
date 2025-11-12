-- Database Schema for PLAN Malaysia Selangor Helpdesk System
-- Created for PHP-based helpdesk system (without Laravel)

-- Create database
CREATE DATABASE IF NOT EXISTS helpdesk_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE helpdesk_db;

-- Table: users (for authentication)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_penuh VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    jawatan VARCHAR(255),
    bahagian VARCHAR(255),
    no_sambungan VARCHAR(50),
    role ENUM('user', 'admin', 'staff') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: officers (pegawai penerima aduan)
CREATE TABLE IF NOT EXISTS officers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    no_telefon VARCHAR(50),
    status ENUM('bertugas', 'tidak_bertugas') DEFAULT 'bertugas',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: complaints (aduan/cadangan)
CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_number VARCHAR(50) NOT NULL UNIQUE,
    jenis ENUM('aduan', 'cadangan') NOT NULL,
    perkara VARCHAR(500) NOT NULL,
    keterangan TEXT NOT NULL,

    -- User details
    user_id INT,
    nama_pengadu VARCHAR(255) NOT NULL,
    alamat TEXT,
    no_telefon VARCHAR(50),
    poskod VARCHAR(10),
    jawatan VARCHAR(255),
    bahagian VARCHAR(255),
    tingkat VARCHAR(50),
    email VARCHAR(255) NOT NULL,
    no_sambungan VARCHAR(50),

    -- Asset details
    jenis_aset VARCHAR(100),
    no_pendaftaran_aset VARCHAR(100),
    pengguna_akhir VARCHAR(255),
    tarikh_kerosakan DATE,
    perihal_kerosakan VARCHAR(255),
    perihal_kerosakan_value VARCHAR(100),

    -- Officer
    officer_id INT,
    pegawai_penerima VARCHAR(255),

    -- Status tracking
    status ENUM('pending', 'dalam_pemeriksaan', 'sedang_dibaiki', 'selesai', 'dibatalkan') DEFAULT 'pending',
    priority ENUM('rendah', 'sederhana', 'tinggi', 'kritikal') DEFAULT 'sederhana',
    progress INT DEFAULT 0,

    -- Feedback
    rating ENUM('cemerlang', 'baik', 'memuaskan', 'tidak_memuaskan') NULL,
    feedback_comment TEXT NULL,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (officer_id) REFERENCES officers(id) ON DELETE SET NULL,
    INDEX idx_ticket_number (ticket_number),
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_jenis (jenis),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: complaint_status_history (tracking status changes)
CREATE TABLE IF NOT EXISTS complaint_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    status VARCHAR(100) NOT NULL,
    keterangan TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_complaint_id (complaint_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: attachments (file uploads)
CREATE TABLE IF NOT EXISTS attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(100),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    INDEX idx_complaint_id (complaint_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    complaint_id INT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default officers
INSERT INTO officers (nama, email, status) VALUES
('En. Ahmad Bin Abdullah', 'ahmad@jpbdselangor.gov.my', 'bertugas'),
('Pn. Siti Aminah Binti Hassan', 'siti@jpbdselangor.gov.my', 'bertugas'),
('En. Raj Kumar A/L Subramaniam', 'raj@jpbdselangor.gov.my', 'bertugas'),
('Pn. Lim Mei Ling', 'lim@jpbdselangor.gov.my', 'bertugas');

-- Insert default admin user (password: admin123)
INSERT INTO users (nama_penuh, email, password, jawatan, bahagian, role, status) VALUES
('Administrator', 'admin@jpbdselangor.gov.my', '$2y$12$8Wos13KhnYKoWOttGDI28OSaxxrS/oAIhtcczMKtMY5rm0EytQF7i', 'Pentadbir Sistem', 'Bahagian IT', 'admin', 'active');

-- Insert sample user (password: user123)
INSERT INTO users (nama_penuh, email, password, jawatan, bahagian, no_sambungan, role, status) VALUES
('Ahmad Bin Abdullah', 'ahmad.user@jpbdselangor.gov.my', '$2y$12$0ploemIfcfDq/W/calvyx.oQLVsv12pG8sNr2U4Ci3EDgWroPKJyK', 'Pegawai Perancang', 'Bahagian Perancangan', '1234', 'user', 'active');
