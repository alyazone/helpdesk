-- Migration to add Unit IT / Sokongan role and workflow
-- Date: 2025-12-30

-- 1. Add unit_it_sokongan role to users table
ALTER TABLE `users`
MODIFY COLUMN `role` ENUM(
  'user',
  'admin',
  'unit_aduan_dalaman',
  'unit_aset',
  'bahagian_pentadbiran_kewangan',
  'unit_it_sokongan'
) COLLATE utf8mb4_unicode_ci DEFAULT 'user';

-- 2. Add dimajukan_unit_it workflow status to complaints table
ALTER TABLE `complaints`
MODIFY COLUMN `workflow_status` ENUM(
  'baru',
  'disahkan_unit_aduan',
  'dimajukan_unit_aset',
  'dalam_semakan_unit_aset',
  'dimajukan_pegawai_pelulus',
  'diluluskan',
  'ditolak',
  'dimajukan_unit_it',
  'selesai'
) COLLATE utf8mb4_unicode_ci DEFAULT 'baru';

-- 3. Create unit_it_sokongan_officers table
CREATE TABLE IF NOT EXISTS `unit_it_sokongan_officers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_telefon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jawatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('aktif','tidak_aktif') COLLATE utf8mb4_unicode_ci DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Add unit_it_officer_id field to complaints table
ALTER TABLE `complaints`
ADD COLUMN `unit_it_officer_id` int DEFAULT NULL COMMENT 'Unit IT officer assigned to execute the work' AFTER `approval_officer_id`,
ADD COLUMN `unit_it_assigned_at` datetime DEFAULT NULL COMMENT 'When Unit IT was assigned' AFTER `unit_it_officer_id`,
ADD COLUMN `unit_it_completed_by` int DEFAULT NULL COMMENT 'User ID who completed in Unit IT' AFTER `unit_it_assigned_at`,
ADD COLUMN `unit_it_completed_at` datetime DEFAULT NULL COMMENT 'When Unit IT completed the work' AFTER `unit_it_completed_by`;

-- 5. Add foreign key constraints
ALTER TABLE `complaints`
ADD CONSTRAINT `fk_unit_it_officer` FOREIGN KEY (`unit_it_officer_id`) REFERENCES `unit_it_sokongan_officers` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_unit_it_completed_by` FOREIGN KEY (`unit_it_completed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- 6. Insert sample Unit IT officers
INSERT INTO `unit_it_sokongan_officers` (`nama`, `email`, `no_telefon`, `jawatan`, `status`) VALUES
('En. Mohd Faizal Bin Ahmad', 'faizal@jpbdselangor.gov.my', '03-12345681', 'Juruteknik Komputer', 'aktif'),
('Pn. Nurul Ain Binti Hassan', 'nurul.ain@jpbdselangor.gov.my', '03-12345682', 'Pembantu Teknikal IT', 'aktif'),
('En. Rizal Bin Abdullah', 'rizal@jpbdselangor.gov.my', '03-12345683', 'Pegawai IT Sokongan', 'aktif');

-- 7. Insert sample Unit IT user account
INSERT INTO `users` (`nama_penuh`, `email`, `password`, `jawatan`, `bahagian`, `unit`, `role`, `status`) VALUES
('En. Mohd Faizal Bin Ahmad', 'faizal@jpbdselangor.gov.my', '$2y$12$8Wos13KhnYKoWOttGDI28OSaxxrS/oAIhtcczMKtMY5rm0EytQF7i', 'Juruteknik Komputer', 'Unit IT / Sokongan', 'Unit IT / Sokongan', 'unit_it_sokongan', 'active');

-- Migration completed successfully
