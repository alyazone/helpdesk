-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Dec 30, 2025 at 01:02 AM
-- Server version: 8.0.44
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `helpdesk_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `id` int NOT NULL,
  `complaint_id` int NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int NOT NULL,
  `file_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attachments`
--

INSERT INTO `attachments` (`id`, `complaint_id`, `file_name`, `file_original_name`, `file_path`, `file_size`, `file_type`, `uploaded_at`) VALUES
(1, 1, '69142a0abad58_1762929162.jpg', '133930489395046031.jpg', 'C:\\xampp\\htdocs\\helpdesk\\config/../uploads/69142a0abad58_1762929162.jpg', 2169467, 'image/jpeg', '2025-11-12 06:32:42'),
(2, 4, '69142d765358a_1762930038.jpg', '133918535269476498.jpg', 'C:\\xampp\\htdocs\\helpdesk\\config/../uploads/69142d765358a_1762930038.jpg', 2250255, 'image/jpeg', '2025-11-12 06:47:18'),
(3, 5, '69143177ee217_1762931063.jpg', '133976346226136338.jpg', 'C:\\xampp\\htdocs\\helpdesk\\config/../uploads/69143177ee217_1762931063.jpg', 2104476, 'image/jpeg', '2025-11-12 07:04:23'),
(4, 7, '6914389b70afc_1762932891.jpg', '133942525049378609.jpg', 'C:\\xampp\\htdocs\\helpdesk\\config/../uploads/6914389b70afc_1762932891.jpg', 1734687, 'image/jpeg', '2025-11-12 07:34:51'),
(5, 8, '691441cdadec6_1762935245.jpg', '133870493334968169.jpg', 'C:\\xampp\\htdocs\\helpdesk\\config/../uploads/691441cdadec6_1762935245.jpg', 1340528, 'image/jpeg', '2025-11-12 08:14:05'),
(6, 9, '6915314972cab_1762996553.jpg', '133993450216123793.jpg', 'C:\\xampp\\htdocs\\helpdesk\\config/../uploads/6915314972cab_1762996553.jpg', 2470175, 'image/jpeg', '2025-11-13 01:15:53'),
(7, 10, '691aa5d63f12e_1763354070.png', 'selangor-skyline.png', 'C:\\xampp\\htdocs\\helpdesk\\config/../uploads/691aa5d63f12e_1763354070.png', 1058439, 'image/png', '2025-11-17 04:34:30'),
(8, 11, '691acc263e937_1763363878.jpg', 'mbmb.jpg', 'C:\\xampp\\htdocs\\helpdesk\\config/../uploads/691acc263e937_1763363878.jpg', 64282, 'image/jpeg', '2025-11-17 07:17:58'),
(9, 12, '691bcf07b1187_1763430151.jpeg', 'MPJ-Training3.jpeg', 'C:\\xampp\\htdocs\\helpdesk\\config/../uploads/691bcf07b1187_1763430151.jpeg', 99932, 'image/jpeg', '2025-11-18 01:42:31'),
(10, 17, '69531c8082501_1767054464.jpeg', 'AP-MDSB1.jpeg', '/var/www/html/helpdesk/config/../uploads/69531c8082501_1767054464.jpeg', 134940, 'image/jpeg', '2025-12-30 00:27:44');

-- --------------------------------------------------------

--
-- Table structure for table `borang_kerosakan_aset`
--

CREATE TABLE `borang_kerosakan_aset` (
  `id` int NOT NULL,
  `complaint_id` int NOT NULL,
  `jenis_aset` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_siri_pendaftaran_aset` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pengguna_terakhir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tarikh_kerosakan` date DEFAULT NULL,
  `perihal_kerosakan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_jawatan_pengadu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tarikh_pengadu` date DEFAULT NULL,
  `jumlah_kos_penyelenggaraan_terdahulu` decimal(10,2) DEFAULT '0.00',
  `anggaran_kos_penyelenggaraan` decimal(10,2) DEFAULT '0.00',
  `nama_jawatan_pegawai_aset` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tarikh_pegawai_aset` date DEFAULT NULL,
  `syor_ulasan` text COLLATE utf8mb4_unicode_ci,
  `keputusan_status` enum('diluluskan','ditolak','pending') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `keputusan_ulasan` text COLLATE utf8mb4_unicode_ci,
  `keputusan_nama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keputusan_jawatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keputusan_tarikh` date DEFAULT NULL,
  `tandatangan_dijana_komputer` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `borang_kerosakan_aset`
--

INSERT INTO `borang_kerosakan_aset` (`id`, `complaint_id`, `jenis_aset`, `no_siri_pendaftaran_aset`, `pengguna_terakhir`, `tarikh_kerosakan`, `perihal_kerosakan`, `nama_jawatan_pengadu`, `tarikh_pengadu`, `jumlah_kos_penyelenggaraan_terdahulu`, `anggaran_kos_penyelenggaraan`, `nama_jawatan_pegawai_aset`, `tarikh_pegawai_aset`, `syor_ulasan`, `keputusan_status`, `keputusan_ulasan`, `keputusan_nama`, `keputusan_jawatan`, `keputusan_tarikh`, `tandatangan_dijana_komputer`, `created_at`, `updated_at`) VALUES
(4, 14, 'laptop', 'LP-2024-033', 'Alya Wani', '2025-11-18', 'Lain-lain (ICT)', 'Alya Wani - pegawai it', '2025-11-18', 10.00, 49.99, 'Pn. Maznah Binti Marzuki', '2025-12-29', 'Isu telah dipanjangkan kepada pegawai pelulus', 'diluluskan', 'Akan membuat tindakan segera', 'Pn. Alia Binti Mohd Yusof', 'Penolong Pengarah', '2025-12-29', 1, '2025-12-29 08:24:37', '2025-12-29 08:38:17'),
(5, 17, 'lain-lain', 'ICT-2023-001', 'Amar Maaruf', '2025-12-30', 'Lampu', 'Amar Maaruf - Pegawai Teknologi Maklumat', '2025-12-30', 300.00, 999.98, 'Pn. Maznah Binti Marzuki', '2025-12-30', 'Aset boleh diperbaiki dan aduan akan dimajukan kepada pegawai pelulus', 'ditolak', 'Melebihi budget', 'Pn. Alia Binti Mohd Yusof', 'Penolong Pengarah', '2025-12-30', 1, '2025-12-30 00:37:55', '2025-12-30 00:42:52');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int NOT NULL,
  `ticket_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` enum('aduan','cadangan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `perkara` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `nama_pengadu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `no_telefon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `poskod` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jawatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bahagian` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tingkat` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_sambungan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_aset` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_pendaftaran_aset` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pengguna_akhir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tarikh_kerosakan` date DEFAULT NULL,
  `perihal_kerosakan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perihal_kerosakan_value` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `officer_id` int DEFAULT NULL,
  `pegawai_penerima` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','dalam_pemeriksaan','sedang_dibaiki','selesai','dibatalkan') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `workflow_status` enum('baru','disahkan_unit_aduan','dimajukan_unit_aset','dalam_semakan_unit_aset','dimajukan_pegawai_pelulus','diluluskan','ditolak','selesai') COLLATE utf8mb4_unicode_ci DEFAULT 'baru',
  `unit_aduan_verified_by` int DEFAULT NULL,
  `priority` enum('rendah','sederhana','tinggi','kritikal') COLLATE utf8mb4_unicode_ci DEFAULT 'sederhana',
  `progress` int DEFAULT '0',
  `rating` enum('cemerlang','baik','memuaskan','tidak_memuaskan') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feedback_comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  `unit_aduan_verified_at` datetime DEFAULT NULL,
  `dimajukan_ke` int DEFAULT NULL COMMENT 'Unit Aset Officer ID',
  `tindakan_susulan` text COLLATE utf8mb4_unicode_ci,
  `tindakan_kesimpulan` text COLLATE utf8mb4_unicode_ci,
  `unit_aset_officer_id` int DEFAULT NULL,
  `unit_aset_reviewed_at` datetime DEFAULT NULL,
  `pegawai_pelulus_id` int DEFAULT NULL,
  `pegawai_pelulus_reviewed_at` datetime DEFAULT NULL,
  `pegawai_pelulus_status` enum('diluluskan','ditolak','pending') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `unit_aset_processed_by` int DEFAULT NULL COMMENT 'User ID who processed in Unit Aset',
  `unit_aset_processed_at` datetime DEFAULT NULL COMMENT 'When Unit Aset processed',
  `approval_officer_id` int DEFAULT NULL COMMENT 'Approval officer for this complaint'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `ticket_number`, `jenis`, `perkara`, `keterangan`, `user_id`, `nama_pengadu`, `alamat`, `no_telefon`, `poskod`, `jawatan`, `bahagian`, `tingkat`, `email`, `no_sambungan`, `jenis_aset`, `no_pendaftaran_aset`, `pengguna_akhir`, `tarikh_kerosakan`, `perihal_kerosakan`, `perihal_kerosakan_value`, `officer_id`, `pegawai_penerima`, `status`, `workflow_status`, `unit_aduan_verified_by`, `priority`, `progress`, `rating`, `feedback_comment`, `created_at`, `updated_at`, `completed_at`, `unit_aduan_verified_at`, `dimajukan_ke`, `tindakan_susulan`, `tindakan_kesimpulan`, `unit_aset_officer_id`, `unit_aset_reviewed_at`, `pegawai_pelulus_id`, `pegawai_pelulus_reviewed_at`, `pegawai_pelulus_status`, `unit_aset_processed_by`, `unit_aset_processed_at`, `approval_officer_id`) VALUES
(1, 'ADU-2025-750', 'aduan', 'test', 'test11', 4, 'test', '', '', '', 'tesrvv', 'testww', '4567', 'test@jpbdselangor.gov.my', '1234', 'scanner', 'ict-009-00', '', '2025-11-12', 'Tidak Boleh Akses Windows', 'tidak_boleh_akses_windows', 1, 'En. Ahmad Bin Abdullah', 'pending', 'baru', NULL, 'sederhana', 0, NULL, NULL, '2025-11-12 06:32:42', '2025-11-12 06:32:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL),
(2, 'ADU-2025-841', 'aduan', 'Test Complaint from Direct PHP', 'This is a test to see if the PHP API works directly', 4, 'Direct Test User', '', '', '', 'Tester', 'IT Department', '5', 'directtest@jpbdselangor.gov.my', '9999', 'komputer', 'DIRECT-TEST-001', '', '2025-01-15', 'Test hardware issue', 'komputer_hang', 1, 'En. Ahmad Bin Abdullah', 'dibatalkan', 'baru', NULL, 'sederhana', 0, NULL, NULL, '2025-11-12 06:41:38', '2025-11-18 01:29:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL),
(3, 'ADU-2025-198', 'aduan', 'Test Complaint', 'This is a test complaint to verify API is working', 4, 'Test User', '', '', '', 'Tester', 'IT', '1', 'test@jpbdselangor.gov.my', '1234', 'komputer', 'TEST-001', '', '2025-01-15', 'Test Issue', 'komputer_hang', 1, 'En. Ahmad Bin Abdullah', 'pending', 'baru', NULL, 'sederhana', 0, NULL, NULL, '2025-11-12 06:44:33', '2025-11-12 06:44:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL),
(4, 'ADU-2025-491', 'aduan', 'test', 'testABC', 4, 'TEST', '', '', '', 'testasd', 'testghi', '43700', 'test@jpbdselangor.gov.my', '123456', 'komputer', 'ICT-2025-006', '', '2025-11-12', 'HDD/SSD Rosak', 'hdd_ssd', 1, 'En. Ahmad Bin Abdullah', 'pending', 'baru', NULL, 'sederhana', 0, NULL, NULL, '2025-11-12 06:47:18', '2025-11-12 06:47:18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL),
(5, 'ADU-2025-485', 'aduan', 'test', 'test11', 4, 'test', '', '', '', 'testabc', 'testghi', '4567', 'test@jpbdselangor.gov.my', '1234', 'network', 'ict-2025-45', '', '2025-11-12', 'Masalah Printer', 'printer', 1, 'En. Ahmad Bin Abdullah', 'dibatalkan', 'baru', NULL, 'sederhana', 0, NULL, NULL, '2025-11-12 07:04:23', '2025-11-18 01:28:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL),
(6, 'ADU-2025-883', 'aduan', 'Test Complaint from Direct PHP', 'This is a test to see if the PHP API works directly', NULL, 'Direct Test User', '', '', '', 'Tester', 'IT Department', '5', 'directtest@jpbdselangor.gov.my', '9999', 'komputer', 'DIRECT-TEST-001', '', '2025-01-15', 'Test hardware issue', 'komputer_hang', 1, 'En. Ahmad Bin Abdullah', 'pending', 'baru', NULL, 'sederhana', 0, NULL, NULL, '2025-11-12 07:06:50', '2025-11-12 07:06:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL),
(7, 'ADU-2025-408', 'aduan', 'ADUAN KEHILANGAN LAPTOP', 'Laptop telah hilang pada tarikh 12/11/2025 pukul 3:30pm di bagunan iTech Tower, Cyberjaya ketika menghadiri conference.', 5, 'Ahmad Ali', '', '', '', 'Pegawai Perancang', 'Bahagian Perancang', '5', 'ahmad.user@jpbdselangor.gov.my', '550', 'laptop', 'LP-2022-001', '', '2025-11-12', 'Lain-lain (ICT)', 'ict_lain_lain', 1, 'En. Ahmad Bin Abdullah', 'selesai', 'baru', NULL, 'sederhana', 100, NULL, NULL, '2025-11-12 07:34:51', '2025-11-18 01:29:45', '2025-11-18 01:29:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL),
(8, 'ADU-2025-973', 'aduan', 'ADUAN KEHILANGAN DAN KEROSAKAN LAPTOP', 'Laptop telah hilang pada sekian sekian tarikh pada pukul 5 petang di bagunan iTech Tower ketika menghadiri mensyuarat', 5, 'AHMAD ALI', '', '', '', 'Pegawai Perancang', 'Bahagian Perancangan', '45', 'ahmad.user@jpbdselangor.gov.my', '0501', 'komputer', 'LP-2023-005', '', '2025-11-12', 'HDD/SSD Rosak', 'hdd_ssd', 1, 'En. Ahmad Bin Abdullah', 'sedang_dibaiki', 'baru', NULL, 'sederhana', 75, NULL, NULL, '2025-11-12 08:14:05', '2025-11-18 01:30:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL),
(9, 'ADU-2025-144', 'aduan', 'ADUAN KEROSAKAN PRINTER', 'Printer berjenama HP-V001 telah rosak pada tarikh 13/11/2025, pukul 5pm telah rosak mengakibatkan kertas tidak boleh keluar', 5, 'ALYA MAISARAH', '', '', '', 'SYSTEM ENGINEER', 'IT', '5', 'alya@jpbdselangor.gov.my', '2504', 'printer', 'PR-2025-001', '', '2025-11-13', 'Masalah Printer', 'printer', 13, 'En. Ahmad Bin Abdullah', 'selesai', 'baru', NULL, 'sederhana', 100, 'cemerlang', '', '2025-11-13 01:15:53', '2025-11-21 02:26:33', '2025-11-21 02:26:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL),
(10, 'ADU-2025-421', 'aduan', 'COMPLAINT LAPTOP TERLAMPAU ROSAK', 'ROSAK ROSAK', 7, 'Adlin Nabila', '', '', '', 'IT Pegawai', 'BAHAGIAN IT', '4', 'linlin@jpbdselangor.gov.my', '0023', 'komputer', 'LP-2024-002', '', '2025-11-17', 'HDD/SSD Rosak', 'hdd_ssd', 13, 'En. Ahmad Bin Abdullah', 'selesai', 'baru', NULL, 'sederhana', 100, 'baik', '', '2025-11-17 04:34:30', '2025-11-18 02:31:08', '2025-11-18 02:28:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL),
(11, 'ADU-2025-465', 'aduan', 'PRINTER ROSAK', 'PRINTER ROSAK PADA PUKUL 2 PETANG DI BILIK PRINTER', 7, 'ADLIN NABILA', '', '', '', 'IT PEGAWAI', 'BAHAGIAN IT', '4', 'linlin@jpbdselangor.gov.my', '3456', 'printer', 'PR-2023-024', '', '2025-11-17', 'Masalah Printer', 'printer', 13, 'En. Ahmad Bin Abdullah', 'selesai', 'baru', NULL, 'sederhana', 100, 'cemerlang', '', '2025-11-17 07:17:58', '2025-11-18 04:04:01', '2025-11-18 04:03:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL),
(12, 'ADU-2025-184', 'cadangan', 'CADANGAN PENAMBAHBAIKAN KERUSI', 'KERUSI SEDIA ADA TELAH USANG DAN LAMA', 8, 'Syazwani Ili', '', '', '', 'Pegawai IT Professional', 'Bahagian IT', '5', 'ili@jpbdselangor.gov.my', '345', 'lain-lain', 'SR-2023-004', '', '2025-11-18', 'Lain-lain (Bangunan)', 'bangunan_lain_lain', 13, 'En. Ahmad Bin Abdullah', 'selesai', 'baru', NULL, 'kritikal', 100, 'tidak_memuaskan', '', '2025-11-18 01:42:31', '2025-11-18 04:08:07', '2025-11-18 04:07:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL),
(13, 'ADU-2025-151', 'aduan', 'ADUAN PERKAKASAN TIDAK BERFUNGSI', 'PERKAKASAN TIDAK BERFUNGSI PADA TARIKH 18/11/2025 PADA PUKUL 9:47 PAGI', 8, 'SYAZWANI ILI', '', '', '', 'Pegawai IT Professional', 'Bahagian IT', '5', 'ili@jpbdselangor.gov.my', '3455', 'network', 'PR-2025-45', '', '2025-11-18', 'Masalah SPECS', 'specs_problem', 13, 'En. Ahmad Bin Abdullah', 'selesai', 'baru', NULL, 'tinggi', 100, 'cemerlang', '', '2025-11-18 01:49:42', '2025-11-18 04:27:08', '2025-11-18 04:25:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL),
(14, 'ADU-2025-716', 'cadangan', 'CADANGAN PENUKARAN LAPTOP', 'PENUKARAN LAPTOP TERKINI KEPADA LAPTOP YANG LEBIH BAIK DAN CANGGIH', 8, 'Alya Wani', '', '', '', 'pegawai it', 'bahagian it', '4', 'ili2@jpbdselangor.gov.my', '1234', 'laptop', 'LP-2024-033', '', '2025-11-18', 'Lain-lain (ICT)', 'ict_lain_lain', 13, 'Cik Alya Maisarah', 'pending', 'diluluskan', 14, 'sederhana', 0, NULL, NULL, '2025-11-18 04:50:14', '2025-12-29 08:38:17', NULL, '2025-12-29 07:26:38', 1, 'aduan telah diterima dan perlukan tindakan segera', NULL, NULL, NULL, 16, '2025-12-29 08:38:17', 'diluluskan', 15, '2025-12-29 08:24:37', 16),
(15, 'ADU-2025-787', 'aduan', 'ADUAN TESTING', 'ADUAN ABCVD', 9, 'Alya Maisarah', '', '', '', 'Pegawai IT', 'IT', '4', 'alya@jpbdselangor.gov.my', '1234', 'laptop', 'ICT-2023-001', '', '2025-12-02', 'Masalah Login Sistem', 'masalah_login', 13, 'Cik Alya Maisarah', 'selesai', 'dimajukan_unit_aset', 14, 'rendah', 100, 'cemerlang', '', '2025-12-02 05:48:04', '2025-12-29 07:25:59', '2025-12-02 06:08:51', '2025-12-29 07:25:59', 1, 'aduan telah diterima', NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL),
(16, 'ADU-2025-463', 'aduan', 'ADUAN LAPTOP ROSAK', 'LAPTOP ROSAK KERANA TELAH MASUK AIR', 10, 'Naquib Haziq', '', '', '', 'Pegawai Teknologi', 'perancangan', '3', 'naquib@jpbdselangor.gov.my', '1998', 'laptop', 'LP-2025-003', '', '2025-12-07', 'Komputer Hang', 'komputer_hang', 13, 'Cik Alya Maisarah', 'pending', 'dimajukan_unit_aset', 14, 'sederhana', 0, NULL, NULL, '2025-12-07 01:50:48', '2025-12-29 07:24:42', NULL, '2025-12-29 07:24:42', 2, 'aduan telah diterima dan perlukan tindakan segera', NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL),
(17, 'ADU-2025-391', 'cadangan', 'CADANGAN MEMULIHKAN LAMPU DI BILIK SERVER', 'Lampu bilik di server telah rosak dan menyebabkan kerja-kerja terganggu', 17, 'Amar Maaruf', '', '', '', 'Pegawai Teknologi Maklumat', 'pembangunan', '2', 'amar@jpbdselangor.gov.my', '1335', 'lain-lain', 'ICT-2023-001', '', '2025-12-30', 'Lampu', 'lampu_bangunan', 13, 'Cik Alya Maisarah', 'pending', 'ditolak', 14, 'sederhana', 0, NULL, NULL, '2025-12-30 00:27:44', '2025-12-30 00:42:52', NULL, '2025-12-30 00:32:11', 1, 'aduan telah diterima dan akan dimajukan dengan segera', NULL, NULL, NULL, 16, '2025-12-30 00:42:52', 'ditolak', 15, '2025-12-30 00:39:06', 16);

-- --------------------------------------------------------

--
-- Table structure for table `complaint_status_history`
--

CREATE TABLE `complaint_status_history` (
  `id` int NOT NULL,
  `complaint_id` int NOT NULL,
  `status` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaint_status_history`
--

INSERT INTO `complaint_status_history` (`id`, `complaint_id`, `status`, `keterangan`, `created_by`, `created_at`) VALUES
(1, 1, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 4, '2025-11-12 06:32:42'),
(2, 2, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 4, '2025-11-12 06:41:38'),
(3, 3, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 4, '2025-11-12 06:44:33'),
(4, 4, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 4, '2025-11-12 06:47:18'),
(5, 5, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 4, '2025-11-12 07:04:23'),
(6, 6, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', NULL, '2025-11-12 07:06:50'),
(7, 7, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 5, '2025-11-12 07:34:51'),
(8, 8, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 5, '2025-11-12 08:14:05'),
(9, 9, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 5, '2025-11-13 01:15:53'),
(10, 10, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 7, '2025-11-17 04:34:30'),
(11, 11, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 7, '2025-11-17 07:17:58'),
(12, 11, 'dalam_pemeriksaan', '', 4, '2025-11-18 00:20:30'),
(13, 5, 'dibatalkan', '', 4, '2025-11-18 01:28:59'),
(14, 2, 'dibatalkan', '', 4, '2025-11-18 01:29:25'),
(15, 7, 'selesai', '', 4, '2025-11-18 01:29:45'),
(16, 8, 'sedang_dibaiki', '', 4, '2025-11-18 01:30:22'),
(17, 12, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 8, '2025-11-18 01:42:31'),
(18, 12, 'sedang_dibaiki', 'Kerusi akan digantikan dengan baharu', 4, '2025-11-18 01:43:42'),
(19, 13, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 8, '2025-11-18 01:49:42'),
(20, 13, 'dalam_pemeriksaan', '', 4, '2025-11-18 02:22:20'),
(21, 10, 'selesai', '', 4, '2025-11-18 02:28:31'),
(22, 11, 'selesai', '', 4, '2025-11-18 04:03:36'),
(23, 12, 'selesai', '', 4, '2025-11-18 04:07:36'),
(24, 13, 'selesai', '', 4, '2025-11-18 04:25:34'),
(25, 14, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 8, '2025-11-18 04:50:14'),
(26, 9, 'sedang_dibaiki', '', 4, '2025-11-21 02:22:57'),
(27, 9, 'selesai', '', 4, '2025-11-21 02:26:10'),
(28, 15, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 9, '2025-12-02 05:48:04'),
(29, 15, 'dalam_pemeriksaan', '', 4, '2025-12-02 06:05:10'),
(30, 15, 'selesai', '', 4, '2025-12-02 06:08:51'),
(31, 16, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 10, '2025-12-07 01:50:48'),
(32, 17, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 17, '2025-12-30 00:27:44');

-- --------------------------------------------------------

--
-- Table structure for table `dokumen_unit_aduan`
--

CREATE TABLE `dokumen_unit_aduan` (
  `id` int NOT NULL,
  `complaint_id` int NOT NULL,
  `no_rujukan_fail` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dimajukan_ke_officer_id` int DEFAULT NULL COMMENT 'Unit Aset Officer',
  `tindakan_susulan` text COLLATE utf8mb4_unicode_ci,
  `tindakan_kesimpulan` text COLLATE utf8mb4_unicode_ci,
  `tarikh` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dokumen_unit_aduan`
--

INSERT INTO `dokumen_unit_aduan` (`id`, `complaint_id`, `no_rujukan_fail`, `dimajukan_ke_officer_id`, `tindakan_susulan`, `tindakan_kesimpulan`, `tarikh`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 16, 'ADU-2025-463', 2, 'aduan telah diterima dan perlukan tindakan segera', 'aduan telah diterima dan perlukan tindakan segera', '2025-12-29', 14, '2025-12-29 07:24:42', '2025-12-29 07:24:42'),
(2, 15, 'ADU-2025-787', 1, 'aduan telah diterima', 'aduan telah diterima', '2025-12-29', 14, '2025-12-29 07:25:59', '2025-12-29 07:25:59'),
(3, 14, 'ADU-2025-716', 1, 'aduan telah diterima dan perlukan tindakan segera', 'aduan telah diterima dan perlukan tindakan segera', '2025-12-29', 14, '2025-12-29 07:26:38', '2025-12-29 07:26:38'),
(4, 17, 'ADU-2025-391', 1, 'aduan telah diterima dan akan dimajukan dengan segera', 'aduan telah diterima dan akan dimajukan dengan segera', '2025-12-30', 14, '2025-12-30 00:32:11', '2025-12-30 00:32:11');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `complaint_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('info','success','warning','error') COLLATE utf8mb4_unicode_ci DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `officers`
--

CREATE TABLE `officers` (
  `id` int NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_telefon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('bertugas','tidak_bertugas') COLLATE utf8mb4_unicode_ci DEFAULT 'bertugas',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `officers`
--

INSERT INTO `officers` (`id`, `nama`, `email`, `no_telefon`, `status`, `created_at`, `updated_at`) VALUES
(1, 'En. Ahmad Bin Abdullah', 'ahmad@jpbdselangor.gov.my', '', 'tidak_bertugas', '2025-11-12 02:33:22', '2025-11-18 01:46:01'),
(8, 'Pn. Lim Mei Ling', 'lim@jpbdselangor.gov.my', '', 'tidak_bertugas', '2025-11-12 02:34:23', '2025-11-18 01:35:39'),
(13, 'Cik Alya Maisarah', 'alya@jpbdselangor.gov.my', '0123937847', 'bertugas', '2025-11-18 01:32:37', '2025-11-18 01:35:51');

-- --------------------------------------------------------

--
-- Table structure for table `unit_aset_officers`
--

CREATE TABLE `unit_aset_officers` (
  `id` int NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_telefon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jawatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('aktif','tidak_aktif') COLLATE utf8mb4_unicode_ci DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `unit_aset_officers`
--

INSERT INTO `unit_aset_officers` (`id`, `nama`, `email`, `no_telefon`, `jawatan`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Pn. Maznah Binti Marzuki', 'maznah@jpbdselangor.gov.my', '03-12345678', 'Pembantu Tadbir', 'aktif', '2025-12-29 04:12:41', '2025-12-29 04:12:41'),
(2, 'En. Ahmad Bin Hassan', 'ahmad.hassan@jpbdselangor.gov.my', '03-12345679', 'Pegawai Aset', 'aktif', '2025-12-29 04:12:41', '2025-12-29 04:12:41'),
(3, 'Pn. Siti Nurhaliza Binti Abdullah', 'siti.nurhaliza@jpbdselangor.gov.my', '03-12345680', 'Pegawai Teknikal', 'aktif', '2025-12-29 04:12:41', '2025-12-29 04:12:41'),
(4, 'Pn. Maznah Binti Marzuki', 'maznah@jpbdselangor.gov.my', '03-12345678', 'Pembantu Tadbir', 'aktif', '2025-12-29 06:32:32', '2025-12-29 06:32:32'),
(5, 'En. Ahmad Bin Hassan', 'ahmad.hassan@jpbdselangor.gov.my', '03-12345679', 'Pegawai Aset', 'aktif', '2025-12-29 06:32:32', '2025-12-29 06:32:32'),
(6, 'Pn. Siti Nurhaliza Binti Abdullah', 'siti.nurhaliza@jpbdselangor.gov.my', '03-12345680', 'Pegawai Teknikal', 'aktif', '2025-12-29 06:32:32', '2025-12-29 06:32:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama_penuh` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jawatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bahagian` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_sambungan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tingkat` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('user','admin','unit_aduan_dalaman','unit_aset','bahagian_pentadbiran_kewangan') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `reset_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama_penuh`, `email`, `password`, `jawatan`, `bahagian`, `unit`, `no_sambungan`, `tingkat`, `role`, `status`, `created_at`, `updated_at`, `reset_token`, `reset_token_expires`) VALUES
(4, 'Administrator', 'admin@jpbdselangor.gov.my', '$2y$12$8Wos13KhnYKoWOttGDI28OSaxxrS/oAIhtcczMKtMY5rm0EytQF7i', 'Pentadbir Sistem', 'Bahagian IT', NULL, NULL, NULL, 'admin', 'active', '2025-11-12 02:35:04', '2025-11-12 03:42:51', NULL, NULL),
(5, 'Ahmad Bin Abdullah', 'ahmad.user@jpbdselangor.gov.my', '$2y$12$0ploemIfcfDq/W/calvyx.oQLVsv12pG8sNr2U4Ci3EDgWroPKJyK', 'Pegawai Perancang', 'Bahagian Perancangan', NULL, '1234', NULL, 'user', 'active', '2025-11-12 02:35:04', '2025-11-12 03:42:51', NULL, NULL),
(7, 'Adlin Nabila', 'linlin@jpbdselangor.gov.my', '$2y$10$/HHAJ8dO/UywiZDCG3359eoSfKrwa31i.6eRupmVhsbl509Nbol/S', 'Pegawai IT', 'it', NULL, '03-123445', NULL, 'user', 'active', '2025-11-17 04:14:37', '2025-11-17 04:14:37', NULL, NULL),
(8, 'Syazwani Ili', 'ili@jpbdselangor.gov.my', '$2y$10$NcQwx35CPdbHGTttudBifONiheGQcbZnIqloMW5.NPpI3wqUKTJv.', 'Pegawai IT Professional', 'it', NULL, '03-45667889', NULL, 'user', 'active', '2025-11-18 01:39:18', '2025-11-18 01:39:18', NULL, NULL),
(9, 'Alya Maisarah', 'alya@jpbdselangor.gov.my', '$2y$10$NqJvbJIpNFrVX4EfCUQ3S.kecnDlIJx8zqjIaARYhhKnPjq7yP.Hi', 'Pegawai Teknologi', 'it', NULL, '012393785', NULL, 'user', 'active', '2025-12-02 05:36:07', '2025-12-02 05:36:07', NULL, NULL),
(10, 'Naquib Haziq', 'naquib@jpbdselangor.gov.my', '$2y$10$XeEwJmOEx6XYdfZml4bIsO2ROmHQvwROpoBc.BH4YdGrZ97Gi1QcW', 'Pegawai Teknologi', 'perancangan', NULL, '1998', '3', 'user', 'active', '2025-12-04 04:52:43', '2025-12-04 04:52:43', NULL, NULL),
(14, 'En. Azri Hanis Bin Zul', 'azri.hanis@jpbdselangor.gov.my', '$2y$12$8Wos13KhnYKoWOttGDI28OSaxxrS/oAIhtcczMKtMY5rm0EytQF7i', 'Pegawai Unit Aduan Dalaman', 'Unit Aduan Dalaman', 'Unit Aduan Dalaman', NULL, NULL, 'unit_aduan_dalaman', 'active', '2025-12-29 06:32:32', '2025-12-29 06:32:32', NULL, NULL),
(15, 'Pn. Maznah Binti Marzuki', 'maznah@jpbdselangor.gov.my', '$2y$12$8Wos13KhnYKoWOttGDI28OSaxxrS/oAIhtcczMKtMY5rm0EytQF7i', 'Pembantu Tadbir', 'Unit Aset', 'Unit Aset', NULL, NULL, 'unit_aset', 'active', '2025-12-29 06:32:32', '2025-12-29 06:32:32', NULL, NULL),
(16, 'Pn. Alia Binti Mohd Yusof', 'alia.yusof@jpbdselangor.gov.my', '$2y$12$8Wos13KhnYKoWOttGDI28OSaxxrS/oAIhtcczMKtMY5rm0EytQF7i', 'Penolong Pengarah', 'Bahagian Pentadbiran & Kewangan', 'Bahagian Pentadbiran & Kewangan', NULL, NULL, 'bahagian_pentadbiran_kewangan', 'active', '2025-12-29 06:32:32', '2025-12-29 06:32:32', NULL, NULL),
(17, 'Amar Maaruf', 'amar@jpbdselangor.gov.my', '$2y$10$Nzff3eHPKSj10PQO7flWquM.90YZAic/5h4jr0EahR/akQB5HfslS', 'Pegawai Teknologi Maklumat', 'pembangunan', NULL, '1335', '2', 'user', 'active', '2025-12-30 00:22:00', '2025-12-30 00:22:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `workflow_actions`
--

CREATE TABLE `workflow_actions` (
  `id` int NOT NULL,
  `complaint_id` int NOT NULL,
  `action_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_status` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `performed_by` int DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workflow_actions`
--

INSERT INTO `workflow_actions` (`id`, `complaint_id`, `action_type`, `from_status`, `to_status`, `performed_by`, `remarks`, `created_at`) VALUES
(1, 16, 'forward_to_unit_aset', 'baru', 'dimajukan_unit_aset', 14, 'Aduan disahkan dan dimajukan ke Unit Aset', '2025-12-29 07:24:42'),
(2, 15, 'forward_to_unit_aset', 'baru', 'dimajukan_unit_aset', 14, 'Aduan disahkan dan dimajukan ke Unit Aset', '2025-12-29 07:25:59'),
(3, 14, 'forward_to_unit_aset', 'baru', 'dimajukan_unit_aset', 14, 'Aduan disahkan dan dimajukan ke Unit Aset', '2025-12-29 07:26:38'),
(4, 14, 'dimajukan_pegawai_pelulus', 'dimajukan_unit_aset', 'dimajukan_pegawai_pelulus', 15, 'Borang Kerosakan Aset Alih telah lengkap dan dimajukan kepada Pegawai Pelulus untuk keputusan', '2025-12-29 08:24:37'),
(5, 14, 'keputusan_pegawai_pelulus', 'dimajukan_pegawai_pelulus', 'diluluskan', 16, 'Diluluskan oleh Pn. Alia Binti Mohd Yusof - Akan membuat tindakan segera', '2025-12-29 08:38:17'),
(6, 17, 'forward_to_unit_aset', 'baru', 'dimajukan_unit_aset', 14, 'Aduan disahkan dan dimajukan ke Unit Aset', '2025-12-30 00:32:11'),
(7, 17, 'dalam_semakan_unit_aset', 'dimajukan_unit_aset', 'dalam_semakan_unit_aset', 15, 'Unit Aset sedang menyemak dan mengisi Borang Kerosakan Aset Alih', '2025-12-30 00:37:55'),
(8, 17, 'dimajukan_pegawai_pelulus', 'dalam_semakan_unit_aset', 'dimajukan_pegawai_pelulus', 15, 'Borang Kerosakan Aset Alih telah lengkap dan dimajukan kepada Pegawai Pelulus untuk keputusan', '2025-12-30 00:39:06'),
(9, 17, 'keputusan_pegawai_pelulus', 'dimajukan_pegawai_pelulus', 'ditolak', 16, 'Ditolak oleh Pn. Alia Binti Mohd Yusof - Melebihi budget', '2025-12-30 00:42:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_complaint_id` (`complaint_id`);

--
-- Indexes for table `borang_kerosakan_aset`
--
ALTER TABLE `borang_kerosakan_aset`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_complaint_id` (`complaint_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_number` (`ticket_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `officer_id` (`officer_id`),
  ADD KEY `idx_ticket_number` (`ticket_number`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_jenis` (`jenis`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `fk_unit_aduan_verified_by` (`unit_aduan_verified_by`),
  ADD KEY `fk_dimajukan_ke` (`dimajukan_ke`),
  ADD KEY `fk_unit_aset_officer` (`unit_aset_officer_id`),
  ADD KEY `fk_pegawai_pelulus` (`pegawai_pelulus_id`),
  ADD KEY `fk_unit_aset_processed_by` (`unit_aset_processed_by`),
  ADD KEY `fk_approval_officer` (`approval_officer_id`);

--
-- Indexes for table `complaint_status_history`
--
ALTER TABLE `complaint_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_complaint_id` (`complaint_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `dokumen_unit_aduan`
--
ALTER TABLE `dokumen_unit_aduan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dimajukan_ke_officer_id` (`dimajukan_ke_officer_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_complaint_id` (`complaint_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_id` (`complaint_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `officers`
--
ALTER TABLE `officers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `unit_aset_officers`
--
ALTER TABLE `unit_aset_officers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_reset_token` (`reset_token`);

--
-- Indexes for table `workflow_actions`
--
ALTER TABLE `workflow_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `performed_by` (`performed_by`),
  ADD KEY `idx_complaint_id` (`complaint_id`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `borang_kerosakan_aset`
--
ALTER TABLE `borang_kerosakan_aset`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `complaint_status_history`
--
ALTER TABLE `complaint_status_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `dokumen_unit_aduan`
--
ALTER TABLE `dokumen_unit_aduan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `officers`
--
ALTER TABLE `officers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `unit_aset_officers`
--
ALTER TABLE `unit_aset_officers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `workflow_actions`
--
ALTER TABLE `workflow_actions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attachments`
--
ALTER TABLE `attachments`
  ADD CONSTRAINT `attachments_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `borang_kerosakan_aset`
--
ALTER TABLE `borang_kerosakan_aset`
  ADD CONSTRAINT `borang_kerosakan_aset_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_approval_officer` FOREIGN KEY (`approval_officer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_dimajukan_ke` FOREIGN KEY (`dimajukan_ke`) REFERENCES `unit_aset_officers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pegawai_pelulus` FOREIGN KEY (`pegawai_pelulus_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_unit_aduan_verified_by` FOREIGN KEY (`unit_aduan_verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_unit_aset_officer` FOREIGN KEY (`unit_aset_officer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_unit_aset_processed_by` FOREIGN KEY (`unit_aset_processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `complaint_status_history`
--
ALTER TABLE `complaint_status_history`
  ADD CONSTRAINT `complaint_status_history_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaint_status_history_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `dokumen_unit_aduan`
--
ALTER TABLE `dokumen_unit_aduan`
  ADD CONSTRAINT `dokumen_unit_aduan_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dokumen_unit_aduan_ibfk_2` FOREIGN KEY (`dimajukan_ke_officer_id`) REFERENCES `unit_aset_officers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dokumen_unit_aduan_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `workflow_actions`
--
ALTER TABLE `workflow_actions`
  ADD CONSTRAINT `workflow_actions_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `workflow_actions_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
