-- Migration: Multi-Unit Workflow System
-- Date: 2025-12-29
-- Description: Adds support for three-unit workflow (Unit Aduan Dalaman, Unit Aset, Bahagian Pentadbiran & Kewangan)

USE helpdesk_db;

-- 1. Update users table to support new roles
ALTER TABLE users
MODIFY COLUMN role ENUM('user', 'admin', 'unit_aduan_dalaman', 'unit_aset', 'bahagian_pentadbiran_kewangan') DEFAULT 'user';

-- Add unit field to users table
ALTER TABLE users
ADD COLUMN unit VARCHAR(100) DEFAULT NULL AFTER bahagian;

-- 2. Create table for Unit Aset Officers
CREATE TABLE IF NOT EXISTS unit_aset_officers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    no_telefon VARCHAR(50),
    jawatan VARCHAR(255),
    status ENUM('aktif', 'tidak_aktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Update complaints table with new workflow fields
ALTER TABLE complaints
ADD COLUMN workflow_status ENUM(
    'baru',  -- New complaint
    'disahkan_unit_aduan',  -- Verified by Unit Aduan Dalaman
    'dimajukan_unit_aset',  -- Forwarded to Unit Aset
    'dalam_semakan_unit_aset',  -- Under review by Unit Aset
    'dimajukan_pegawai_pelulus',  -- Forwarded to Approval Officer
    'diluluskan',  -- Approved
    'ditolak',  -- Rejected
    'selesai'  -- Completed
) DEFAULT 'baru' AFTER status;

-- Add workflow tracking fields
ALTER TABLE complaints
ADD COLUMN unit_aduan_verified_by INT DEFAULT NULL AFTER workflow_status,
ADD COLUMN unit_aduan_verified_at DATETIME DEFAULT NULL,
ADD COLUMN dimajukan_ke INT DEFAULT NULL COMMENT 'Unit Aset Officer ID',
ADD COLUMN tindakan_susulan TEXT DEFAULT NULL,
ADD COLUMN tindakan_kesimpulan TEXT DEFAULT NULL,
ADD COLUMN unit_aset_officer_id INT DEFAULT NULL,
ADD COLUMN unit_aset_reviewed_at DATETIME DEFAULT NULL,
ADD COLUMN pegawai_pelulus_id INT DEFAULT NULL,
ADD COLUMN pegawai_pelulus_reviewed_at DATETIME DEFAULT NULL,
ADD COLUMN pegawai_pelulus_status ENUM('diluluskan', 'ditolak', 'pending') DEFAULT 'pending';

-- Add foreign keys
ALTER TABLE complaints
ADD CONSTRAINT fk_unit_aduan_verified_by FOREIGN KEY (unit_aduan_verified_by) REFERENCES users(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_dimajukan_ke FOREIGN KEY (dimajukan_ke) REFERENCES unit_aset_officers(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_unit_aset_officer FOREIGN KEY (unit_aset_officer_id) REFERENCES users(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_pegawai_pelulus FOREIGN KEY (pegawai_pelulus_id) REFERENCES users(id) ON DELETE SET NULL;

-- 4. Create Dokumen Unit Aduan table
CREATE TABLE IF NOT EXISTS dokumen_unit_aduan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    no_rujukan_fail VARCHAR(100),
    dimajukan_ke_officer_id INT COMMENT 'Unit Aset Officer',
    tindakan_susulan TEXT,
    tindakan_kesimpulan TEXT,
    tarikh DATE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    FOREIGN KEY (dimajukan_ke_officer_id) REFERENCES unit_aset_officers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_complaint_id (complaint_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Create Borang Aduan Kerosakan Aset Alih table
CREATE TABLE IF NOT EXISTS borang_kerosakan_aset (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,

    -- Bahagian I (Untuk diisi oleh Pengadu)
    jenis_aset VARCHAR(100),
    no_siri_pendaftaran_aset VARCHAR(100),
    pengguna_terakhir VARCHAR(255),
    tarikh_kerosakan DATE,
    perihal_kerosakan VARCHAR(255),
    nama_jawatan_pengadu VARCHAR(255),
    tarikh_pengadu DATE,

    -- Bahagian II (Untuk diisi oleh Pegawai Aset/Pegawai Teknikal)
    jumlah_kos_penyelenggaraan_terdahulu DECIMAL(10,2) DEFAULT 0.00,
    anggaran_kos_penyelenggaraan DECIMAL(10,2) DEFAULT 0.00,
    nama_jawatan_pegawai_aset VARCHAR(255),
    tarikh_pegawai_aset DATE,
    syor_ulasan TEXT,

    -- Bahagian III (Keputusan Ketua Jabatan/Bahagian/Seksyen/Unit)
    keputusan_status ENUM('diluluskan', 'ditolak', 'pending') DEFAULT 'pending',
    keputusan_ulasan TEXT,
    keputusan_nama VARCHAR(255),
    keputusan_jawatan VARCHAR(255),
    keputusan_tarikh DATE,
    tandatangan_dijana_komputer BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    INDEX idx_complaint_id (complaint_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Create workflow actions log table
CREATE TABLE IF NOT EXISTS workflow_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    from_status VARCHAR(100),
    to_status VARCHAR(100),
    performed_by INT,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_complaint_id (complaint_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Insert default Unit Aset Officers
INSERT INTO unit_aset_officers (nama, email, no_telefon, jawatan, status) VALUES
('Pn. Maznah Binti Marzuki', 'maznah@jpbdselangor.gov.my', '03-12345678', 'Pembantu Tadbir', 'aktif'),
('En. Ahmad Bin Hassan', 'ahmad.hassan@jpbdselangor.gov.my', '03-12345679', 'Pegawai Aset', 'aktif'),
('Pn. Siti Nurhaliza Binti Abdullah', 'siti.nurhaliza@jpbdselangor.gov.my', '03-12345680', 'Pegawai Teknikal', 'aktif');

-- 8. Create default users for each unit
-- Unit Aduan Dalaman - En. Azri Hanis Bin Zul
INSERT INTO users (nama_penuh, email, password, jawatan, bahagian, unit, role, status) VALUES
('En. Azri Hanis Bin Zul', 'azri.hanis@jpbdselangor.gov.my', '$2y$12$8Wos13KhnYKoWOttGDI28OSaxxrS/oAIhtcczMKtMY5rm0EytQF7i', 'Pegawai Unit Aduan Dalaman', 'Unit Aduan Dalaman', 'Unit Aduan Dalaman', 'unit_aduan_dalaman', 'active')
ON DUPLICATE KEY UPDATE role = 'unit_aduan_dalaman', unit = 'Unit Aduan Dalaman';

-- Unit Aset
INSERT INTO users (nama_penuh, email, password, jawatan, bahagian, unit, role, status) VALUES
('Pn. Maznah Binti Marzuki', 'maznah@jpbdselangor.gov.my', '$2y$12$8Wos13KhnYKoWOttGDI28OSaxxrS/oAIhtcczMKtMY5rm0EytQF7i', 'Pembantu Tadbir', 'Unit Aset', 'Unit Aset', 'unit_aset', 'active')
ON DUPLICATE KEY UPDATE role = 'unit_aset', unit = 'Unit Aset';

-- Bahagian Pentadbiran & Kewangan (Pegawai Pelulus Aset)
INSERT INTO users (nama_penuh, email, password, jawatan, bahagian, unit, role, status) VALUES
('Pn. Alia Binti Mohd Yusof', 'alia.yusof@jpbdselangor.gov.my', '$2y$12$8Wos13KhnYKoWOttGDI28OSaxxrS/oAIhtcczMKtMY5rm0EytQF7i', 'Penolong Pengarah', 'Bahagian Pentadbiran & Kewangan', 'Bahagian Pentadbiran & Kewangan', 'bahagian_pentadbiran_kewangan', 'active')
ON DUPLICATE KEY UPDATE role = 'bahagian_pentadbiran_kewangan', unit = 'Bahagian Pentadbiran & Kewangan';

-- Note: Default password for all test accounts is 'admin123'
-- Users should change their passwords after first login
