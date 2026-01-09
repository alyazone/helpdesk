-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Jan 09, 2026 at 04:18 AM
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
(12, 25, '6953a88658ccb_1767090310.jpeg', 'AP-MDSB.jpeg', '/var/www/html/helpdesk/config/../uploads/6953a88658ccb_1767090310.jpeg', 109153, 'image/jpeg', '2025-12-30 10:25:10'),
(13, 26, '695470e185684_1767141601.png', 'Screenshot 2025-12-30 235413.png', '/var/www/html/helpdesk/config/../uploads/695470e185684_1767141601.png', 140241, 'image/png', '2025-12-31 00:40:01');

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
(10, 25, 'lain-lain', 'ICT-2025-012', 'Adlin Nabila', '2025-12-30', 'Lampu', 'Adlin Nabila - Pegawai Teknologi Maklumat', '2025-12-30', 100.00, 200.00, 'Pn. Maznah Binti Marzuki', '2025-12-30', 'Disyorkan untuk perbaiki. Aduan akan dimajukan kepada pegawai pelulusan', 'diluluskan', 'diluluskan untuk tindakan selanjutnya', 'Pn. Alia Binti Mohd Yusof', 'Penolong Pengarah', '2025-12-30', 1, '2025-12-30 10:30:39', '2025-12-30 10:34:58'),
(12, 26, 'projector', 'PR-2021-025', 'Syazwani Ili', '2025-12-31', 'Lain-lain (ICT)', 'Syazwani Ili - Pegawai Bandar Perancang', '2025-12-31', 80.00, 100.00, 'Pn. Maznah Binti Marzuki', '2025-12-31', 'aset boleh diperbaiki setelah mendapat kelulusan', 'diluluskan', 'aduan diluluskan untuk tindakan selanjutnya', 'Pn. Alia Binti Mohd Yusof', 'Penolong Pengarah', '2025-12-31', 1, '2025-12-31 02:06:31', '2025-12-31 02:10:21'),
(13, 28, 'lain-lain', 'ICT-2023-041', 'Alya Azuan', '2025-12-31', 'Lain-lain (ICT)', 'Alya Azuan - Pegawai Bandar Perancang', '2025-12-31', 50.00, 100.00, 'Pn. Maznah Binti Marzuki', '2025-12-31', 'aset boleh diperbaiki', 'diluluskan', 'diluluskan', 'Pn. Alia Binti Mohd Yusof', 'Penolong Pengarah', '2025-12-31', 1, '2025-12-31 03:26:06', '2025-12-31 03:36:47'),
(14, 29, 'komputer', 'PC-2025-092', 'Alya Azuan', '2026-01-05', 'Lain-lain (ICT)', 'Alya Azuan - Pegawai Bandar Perancang', '2026-01-05', 20.00, 9.98, 'Pn. Maznah Binti Marzuki', '2026-01-05', 'boleh dibaiki', 'diluluskan', 'diluluskan', 'Pn. Alia Binti Mohd Yusof', 'Penolong Pengarah', '2026-01-05', 1, '2026-01-05 06:57:30', '2026-01-05 06:58:57'),
(15, 31, 'lain-lain', 'ICT-2024-202', 'En. Azri Hanis Bin Zul', '2026-01-07', 'Komputer Hang', 'En. Azri Hanis Bin Zul - Pegawai Unit Aduan Dalaman', '2026-01-07', 10.00, 50.00, 'Pn. Maznah Binti Marzuki', '2026-01-07', 'BOLEH DIBAIKI', 'diluluskan', 'dibaiki', 'Pn. Alia Binti Mohd Yusof', 'Penolong Pengarah', '2026-01-07', 1, '2026-01-07 01:59:53', '2026-01-07 02:03:42'),
(16, 32, 'monitor', 'MN-2023-001', 'Pn. Maznah Binti Marzuki', '2026-01-07', 'Masalah Password', 'Pn. Maznah Binti Marzuki - Pembantu Tadbir', '2026-01-07', 10.00, 20.00, 'Pn. Maznah Binti Marzuki', '2026-01-07', 'disyorkan baiki', 'pending', NULL, NULL, NULL, NULL, 1, '2026-01-07 02:05:30', '2026-01-07 02:05:30'),
(17, 33, 'network', 'NT-201-2033', 'Pn. Alia Binti Mohd Yusof', '2026-01-07', 'Tidak Boleh Boot Windows', 'Pn. Alia Binti Mohd Yusof - Penolong Pengarah', '2026-01-07', 10.00, 20.00, 'Pn. Maznah Binti Marzuki', '2026-01-08', 'boleh dibaiki', 'diluluskan', 'diluluskan', 'Pn. Alia Binti Mohd Yusof', 'Penolong Pengarah', '2026-01-08', 1, '2026-01-08 01:24:19', '2026-01-08 02:00:03'),
(18, 30, 'lain-lain', 'SR-2020-045', 'Alya Azuan', '2026-01-07', 'Lain-lain (ICT)', 'Alya Azuan - Pegawai Bandar Perancang', '2026-01-07', 40.00, 45.00, 'Pn. Maznah Binti Marzuki', '2026-01-08', 'dimajukan kepada pegawai pelulus', 'diluluskan', 'boleh diluluskan', 'Pn. Alia Binti Mohd Yusof', 'Penolong Pengarah', '2026-01-08', 1, '2026-01-08 03:55:37', '2026-01-08 03:57:06'),
(19, 27, 'komputer', 'ICT-2021-033', 'Adam Idris', '2025-12-31', 'Lain-lain (ICT)', 'Adam Idris - Pegawai Bandar Perancang', '2025-12-31', 10.00, 12.00, 'Pn. Maznah Binti Marzuki', '2026-01-08', 'boleh dibaiki', 'diluluskan', 'diluluskan', 'Pn. Alia Binti Mohd Yusof', 'Penolong Pengarah', '2026-01-08', 1, '2026-01-08 03:58:57', '2026-01-08 04:34:14');

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
  `workflow_status` enum('baru','disahkan_unit_aduan','dimajukan_unit_aset','dalam_semakan_unit_aset','dimajukan_pegawai_pelulus','diluluskan','ditolak','dimajukan_unit_it','selesai') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'baru',
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
  `approval_officer_id` int DEFAULT NULL COMMENT 'Approval officer for this complaint',
  `unit_it_officer_id` int DEFAULT NULL COMMENT 'Unit IT officer assigned to execute the work',
  `unit_it_assigned_at` datetime DEFAULT NULL COMMENT 'When Unit IT was assigned',
  `unit_it_completed_by` int DEFAULT NULL COMMENT 'User ID who completed in Unit IT',
  `unit_it_completed_at` datetime DEFAULT NULL COMMENT 'When Unit IT completed the work'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `ticket_number`, `jenis`, `perkara`, `keterangan`, `user_id`, `nama_pengadu`, `alamat`, `no_telefon`, `poskod`, `jawatan`, `bahagian`, `tingkat`, `email`, `no_sambungan`, `jenis_aset`, `no_pendaftaran_aset`, `pengguna_akhir`, `tarikh_kerosakan`, `perihal_kerosakan`, `perihal_kerosakan_value`, `officer_id`, `pegawai_penerima`, `status`, `workflow_status`, `unit_aduan_verified_by`, `priority`, `progress`, `rating`, `feedback_comment`, `created_at`, `updated_at`, `completed_at`, `unit_aduan_verified_at`, `dimajukan_ke`, `tindakan_susulan`, `tindakan_kesimpulan`, `unit_aset_officer_id`, `unit_aset_reviewed_at`, `pegawai_pelulus_id`, `pegawai_pelulus_reviewed_at`, `pegawai_pelulus_status`, `unit_aset_processed_by`, `unit_aset_processed_at`, `approval_officer_id`, `unit_it_officer_id`, `unit_it_assigned_at`, `unit_it_completed_by`, `unit_it_completed_at`) VALUES
(25, 'ADU-2025-065', 'aduan', 'ADUAN KEROSAKAN LAMPU', 'Lampu di bilik server telah rosak', 20, 'Adlin Nabila', '', '', '', 'Pegawai Teknologi Maklumat', 'perancangan', '9', 'adlin@jpbdselangor.gov.my', '2001', 'lain-lain', 'ICT-2025-012', '', '2025-12-30', 'Lampu', 'lampu_bangunan', NULL, 'En. Azri Hanis bin Zul', 'selesai', 'selesai', 14, 'sederhana', 0, NULL, NULL, '2025-12-30 10:25:10', '2025-12-30 10:36:40', '2025-12-30 10:36:40', '2025-12-30 10:26:44', 1, 'Aduan telah diterima. sila semak', NULL, NULL, NULL, 16, '2025-12-30 10:34:58', 'diluluskan', 15, '2025-12-30 10:33:00', 16, 1, '2025-12-30 10:34:58', 18, '2025-12-30 10:36:40'),
(26, 'ADU-2025-280', 'aduan', 'ADUAN KEROSAKAN SKRIN PROJEKTOR', 'Skrin projektor pada bilik meeting 2 telah mengalami kerosakan', 21, 'Syazwani Ili', '', '', '', 'Pegawai Bandar Perancang', 'korporat', '3', 'wani@jpbdselangor.gov.my', '2007', 'projector', 'PR-2021-025', '', '2025-12-31', 'Lain-lain (ICT)', 'ict_lain_lain', NULL, 'En. Azri Hanis bin Zul', 'selesai', 'selesai', 14, 'sederhana', 0, NULL, NULL, '2025-12-31 00:40:01', '2025-12-31 02:14:34', '2025-12-31 02:14:34', '2025-12-31 02:04:32', 1, 'aduan telah diterima', NULL, NULL, NULL, 16, '2025-12-31 02:10:21', 'diluluskan', 15, '2025-12-31 02:08:01', 16, 1, '2025-12-31 02:10:21', 18, '2025-12-31 02:14:34'),
(27, 'ADU-2025-467', 'cadangan', 'CADANGAN KOMPUTER BAHARU UNTUK BILIK SERVER', 'Komputer di bilik server telah lama dan perlu diperbaharui', 19, 'Adam Idris', '', '', '', 'Pegawai Bandar Perancang', 'perancangan', '3', 'adam@jpbdselangor.gov.my', '2005', 'komputer', 'ICT-2021-033', '', '2025-12-31', 'Lain-lain (ICT)', 'ict_lain_lain', NULL, 'En. Azri Hanis bin Zul', 'selesai', 'selesai', 14, 'sederhana', 0, NULL, NULL, '2025-12-31 02:19:10', '2026-01-08 04:35:02', '2026-01-08 04:35:02', '2026-01-08 03:58:13', 1, 'dimajukan unit aset', NULL, NULL, NULL, 16, '2026-01-08 04:34:14', 'diluluskan', 15, '2026-01-08 03:58:57', 16, 1, '2026-01-08 03:58:13', 18, '2026-01-08 04:35:02'),
(28, 'ADU-2025-081', 'aduan', 'ADUAN PRINTER ROSAK', 'Printer telah rosak pada 31 disember 2025', 22, 'Alya Azuan', '', '', '', 'Pegawai Bandar Perancang', 'perancangan', '3', 'alya@jpbdselangor.gov.my', '2001', 'lain-lain', 'ICT-2023-041', '', '2025-12-31', 'Lain-lain (ICT)', 'ict_lain_lain', NULL, 'En. Azri Hanis bin Zul', 'selesai', 'selesai', 14, 'sederhana', 0, 'cemerlang', '', '2025-12-31 03:20:14', '2025-12-31 03:58:17', '2025-12-31 03:50:25', '2025-12-31 03:21:34', 1, 'Aduan telah diterima', NULL, NULL, NULL, 16, '2025-12-31 03:36:47', 'diluluskan', 15, '2025-12-31 03:27:24', 16, 1, '2025-12-31 03:36:47', 18, '2025-12-31 03:50:25'),
(29, 'ADU-2026-186', 'aduan', 'ADUAN TENTANG KEROSAKAN PERKAKAS SERVER', 'PERKAKAS SERVER TELAH ROSAK', 22, 'Alya Azuan', '', '', '', 'Pegawai Bandar Perancang', 'perancangan', '3', 'alya@jpbdselangor.gov.my', '2001', 'komputer', 'PC-2025-092', '', '2026-01-05', 'Lain-lain (ICT)', 'ict_lain_lain', NULL, 'En. Azri Hanis bin Zul', 'selesai', 'selesai', 14, 'sederhana', 0, NULL, NULL, '2026-01-05 06:51:09', '2026-01-05 06:59:42', '2026-01-05 06:59:42', '2026-01-05 06:56:39', 1, 'telah diterima', NULL, NULL, NULL, 16, '2026-01-05 06:58:57', 'diluluskan', 15, '2026-01-05 06:57:30', 16, 1, '2026-01-05 06:58:57', 18, '2026-01-05 06:59:42'),
(30, 'ADU-2026-803', 'aduan', 'ADUAN KEROSAKAN SERVER', 'SERVER ROSAK', 22, 'Alya Azuan', '', '', '', 'Pegawai Bandar Perancang', 'perancangan', '3', 'alya@jpbdselangor.gov.my', '2001', 'lain-lain', 'SR-2020-045', '', '2026-01-07', 'Lain-lain (ICT)', 'ict_lain_lain', NULL, 'Pn. Siti Norhayati Binti Mokti', 'selesai', 'selesai', 14, 'sederhana', 0, NULL, NULL, '2026-01-07 01:44:41', '2026-01-08 04:32:20', '2026-01-08 04:32:20', '2026-01-08 03:54:29', 1, 'dimajukan ke unit aset', NULL, NULL, NULL, 16, '2026-01-08 03:57:06', 'diluluskan', 15, '2026-01-08 03:55:37', 16, 1, '2026-01-08 03:54:29', 18, '2026-01-08 04:32:20'),
(31, 'ADU-2026-084', 'cadangan', 'TEST ADUAN AZRI HANIS', 'TEST ADUAN AZRI HANIS DETAILS', 14, 'En. Azri Hanis Bin Zul', '', '', '', 'Pegawai Unit Aduan Dalaman', 'Bahagian Pentadbiran dan Kewangan', '', 'azri.hanis@jpbdselangor.gov.my', '', 'lain-lain', 'ICT-2024-202', '', '2026-01-07', 'Komputer Hang', 'komputer_hang', NULL, 'Pn. Siti Norhayati Binti Mokti', 'selesai', 'selesai', 14, 'sederhana', 0, 'baik', '', '2026-01-07 01:55:33', '2026-01-07 02:07:07', '2026-01-07 02:06:40', '2026-01-07 01:57:46', 1, 'aduan diterima', NULL, NULL, NULL, 16, '2026-01-07 02:03:42', 'diluluskan', 15, '2026-01-07 01:59:53', 16, 1, '2026-01-07 02:03:42', 18, '2026-01-07 02:06:40'),
(32, 'ADU-2026-724', 'cadangan', 'TEST ADUAN MAZNAH', 'TEST ADUAN MAZNAH DETAILS', 15, 'Pn. Maznah Binti Marzuki', '', '', '', 'Pembantu Tadbir', 'Bahagian Pentadbiran dan Kewangan', '', 'maznah@jpbdselangor.gov.my', '', 'monitor', 'MN-2023-001', '', '2026-01-07', 'Masalah Password', 'password', NULL, 'Pn. Siti Norhayati Binti Mokti', 'pending', 'dimajukan_pegawai_pelulus', 14, 'sederhana', 0, NULL, NULL, '2026-01-07 01:59:10', '2026-01-07 02:05:30', NULL, '2026-01-07 02:00:44', 1, 'boleh dibaiki', NULL, NULL, NULL, NULL, NULL, 'pending', 15, '2026-01-07 02:05:30', 16, NULL, NULL, NULL, NULL),
(33, 'ADU-2026-852', 'cadangan', 'TEST ADUAN ALIA YUSOF', 'TEST ADUAN ALIA YUSOF DETAILS', 16, 'Pn. Alia Binti Mohd Yusof', '', '', '', 'Penolong Pengarah', 'Bahagian Pentadbiran &amp; Kewangan', '', 'alia.yusof@jpbdselangor.gov.my', '', 'network', 'NT-201-2033', '', '2026-01-07', 'Tidak Boleh Boot Windows', 'tidak_boleh_boot_windows', NULL, 'Pn. Siti Norhayati Binti Mokti', 'pending', 'diluluskan', 14, 'sederhana', 0, NULL, NULL, '2026-01-07 02:02:22', '2026-01-08 02:00:03', NULL, '2026-01-08 01:23:24', 1, 'diterima', NULL, NULL, NULL, 16, '2026-01-08 02:00:03', 'diluluskan', 15, '2026-01-08 01:24:19', 16, 1, '2026-01-08 01:23:24', NULL, NULL);

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
(62, 25, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 20, '2025-12-30 10:25:10'),
(63, 25, 'Dimajukan ke Unit Aset', 'Aduan telah disahkan oleh Unit Aduan Dalaman dan dimajukan kepada Unit Aset untuk tindakan selanjutnya', 14, '2025-12-30 10:26:44'),
(64, 25, 'Dalam Semakan Unit Aset', 'Unit Aset sedang menyemak dan mengisi Borang Kerosakan Aset Alih', 15, '2025-12-30 10:30:39'),
(65, 25, 'Dimajukan ke Pegawai Pelulus', 'Borang Kerosakan Aset Alih telah disemak oleh Unit Aset dan dimajukan kepada Pegawai Pelulus untuk keputusan', 15, '2025-12-30 10:33:00'),
(66, 25, 'Diluluskan oleh Pegawai Pelulus', 'Diluluskan oleh Pn. Alia Binti Mohd Yusof. Ulasan: diluluskan untuk tindakan selanjutnya', 16, '2025-12-30 10:34:58'),
(67, 25, 'Dimajukan ke Unit IT / Sokongan', 'Aduan telah dimajukan ke Unit IT / Sokongan untuk pelaksanaan tindakan', 16, '2025-12-30 10:34:58'),
(68, 25, 'Selesai', 'Tindakan telah diselesaikan oleh Unit IT / Sokongan pada 30/12/2025. Catatan: telah selesai', 18, '2025-12-30 10:36:40'),
(75, 26, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 21, '2025-12-31 00:40:01'),
(76, 26, 'Dimajukan ke Unit Aset', 'Aduan telah disahkan oleh Unit Aduan Dalaman dan dimajukan kepada Unit Aset untuk tindakan selanjutnya', 14, '2025-12-31 02:04:32'),
(77, 26, 'Dalam Semakan Unit Aset', 'Unit Aset sedang menyemak dan mengisi Borang Kerosakan Aset Alih', 15, '2025-12-31 02:06:31'),
(78, 26, 'Dimajukan ke Pegawai Pelulus', 'Borang Kerosakan Aset Alih telah disemak oleh Unit Aset dan dimajukan kepada Pegawai Pelulus untuk keputusan', 15, '2025-12-31 02:08:01'),
(79, 26, 'Diluluskan oleh Pegawai Pelulus', 'Diluluskan oleh Pn. Alia Binti Mohd Yusof. Ulasan: aduan diluluskan untuk tindakan selanjutnya', 16, '2025-12-31 02:10:21'),
(80, 26, 'Dimajukan ke Unit IT / Sokongan', 'Aduan telah dimajukan ke Unit IT / Sokongan untuk pelaksanaan tindakan', 16, '2025-12-31 02:10:21'),
(81, 26, 'Selesai', 'Tindakan telah diselesaikan oleh Unit IT / Sokongan pada 31/12/2025. Catatan: projektor telah diperbaiki', 18, '2025-12-31 02:14:34'),
(82, 27, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 19, '2025-12-31 02:19:10'),
(83, 28, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 22, '2025-12-31 03:20:14'),
(84, 28, 'Dimajukan ke Unit Aset', 'Aduan telah disahkan oleh Unit Aduan Dalaman dan dimajukan kepada Unit Aset untuk tindakan selanjutnya', 14, '2025-12-31 03:21:34'),
(85, 28, 'Dalam Semakan Unit Aset', 'Unit Aset sedang menyemak dan mengisi Borang Kerosakan Aset Alih', 15, '2025-12-31 03:26:06'),
(86, 28, 'Dimajukan ke Pegawai Pelulus', 'Borang Kerosakan Aset Alih telah disemak oleh Unit Aset dan dimajukan kepada Pegawai Pelulus untuk keputusan', 15, '2025-12-31 03:27:24'),
(87, 28, 'Diluluskan oleh Pegawai Pelulus', 'Diluluskan oleh Pn. Alia Binti Mohd Yusof. Ulasan: diluluskan', 16, '2025-12-31 03:36:47'),
(88, 28, 'Dimajukan ke Unit IT / Sokongan', 'Aduan telah dimajukan ke Unit IT / Sokongan untuk pelaksanaan tindakan', 16, '2025-12-31 03:36:47'),
(89, 28, 'Selesai', 'Tindakan telah diselesaikan oleh Unit IT / Sokongan pada 31/12/2025. Catatan: selesai', 18, '2025-12-31 03:50:25'),
(90, 29, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 22, '2026-01-05 06:51:09'),
(91, 29, 'Dimajukan ke Unit Aset', 'Aduan telah disahkan oleh Unit Aduan Dalaman dan dimajukan kepada Unit Aset untuk tindakan selanjutnya', 14, '2026-01-05 06:56:39'),
(92, 29, 'Dimajukan ke Pegawai Pelulus', 'Borang Kerosakan Aset Alih telah disemak oleh Unit Aset dan dimajukan kepada Pegawai Pelulus untuk keputusan', 15, '2026-01-05 06:57:30'),
(93, 29, 'Diluluskan oleh Pegawai Pelulus', 'Diluluskan oleh Pn. Alia Binti Mohd Yusof. Ulasan: diluluskan', 16, '2026-01-05 06:58:57'),
(94, 29, 'Dimajukan ke Unit IT / Sokongan', 'Aduan telah dimajukan ke Unit IT / Sokongan untuk pelaksanaan tindakan', 16, '2026-01-05 06:58:57'),
(95, 29, 'Selesai', 'Tindakan telah diselesaikan oleh Unit IT / Sokongan pada 05/01/2026. Catatan: telah selesai laksanakan', 18, '2026-01-05 06:59:42'),
(96, 30, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 22, '2026-01-07 01:44:41'),
(97, 31, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 14, '2026-01-07 01:55:33'),
(98, 31, 'Dimajukan ke Unit Aset', 'Aduan telah disahkan oleh Unit Aduan Dalaman dan dimajukan kepada Unit Aset untuk tindakan selanjutnya', 14, '2026-01-07 01:57:46'),
(99, 32, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 15, '2026-01-07 01:59:10'),
(100, 31, 'Dimajukan ke Pegawai Pelulus', 'Borang Kerosakan Aset Alih telah disemak oleh Unit Aset dan dimajukan kepada Pegawai Pelulus untuk keputusan', 15, '2026-01-07 01:59:53'),
(101, 32, 'Dimajukan ke Unit Aset', 'Aduan telah disahkan oleh Unit Aduan Dalaman dan dimajukan kepada Unit Aset untuk tindakan selanjutnya', 14, '2026-01-07 02:00:44'),
(102, 33, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', 16, '2026-01-07 02:02:22'),
(103, 31, 'Diluluskan oleh Pegawai Pelulus', 'Diluluskan oleh Pn. Alia Binti Mohd Yusof. Ulasan: dibaiki', 16, '2026-01-07 02:03:42'),
(104, 31, 'Dimajukan ke Unit IT / Sokongan', 'Aduan telah dimajukan ke Unit IT / Sokongan untuk pelaksanaan tindakan', 16, '2026-01-07 02:03:42'),
(105, 32, 'Dimajukan ke Pegawai Pelulus', 'Borang Kerosakan Aset Alih telah disemak oleh Unit Aset dan dimajukan kepada Pegawai Pelulus untuk keputusan', 15, '2026-01-07 02:05:30'),
(106, 31, 'Selesai', 'Tindakan telah diselesaikan oleh Unit IT / Sokongan pada 07/01/2026. Catatan: selesai melaksana', 18, '2026-01-07 02:06:40'),
(107, 33, 'Dalam Proses', 'Aduan telah disahkan oleh Unit Aduan Dalaman, ditugaskan kepada pegawai pelaksana, dan dimajukan kepada Unit Aset untuk tindakan selanjutnya', 14, '2026-01-08 01:23:24'),
(108, 33, 'Dimajukan ke Pegawai Pelulus', 'Borang Kerosakan Aset Alih telah disemak oleh Unit Aset dan dimajukan kepada Pegawai Pelulus untuk keputusan', 15, '2026-01-08 01:24:19'),
(109, 33, 'Diluluskan oleh Pegawai Pelulus', 'Diluluskan oleh Pn. Alia Binti Mohd Yusof. Ulasan: diluluskan', 16, '2026-01-08 02:00:03'),
(110, 30, 'Dalam Proses', 'Aduan telah disahkan oleh Unit Aduan Dalaman, ditugaskan kepada pegawai pelaksana, dan dimajukan kepada Unit Aset untuk tindakan selanjutnya', 14, '2026-01-08 03:54:29'),
(111, 30, 'Dimajukan ke Pegawai Pelulus', 'Borang Kerosakan Aset Alih telah disemak oleh Unit Aset dan dimajukan kepada Pegawai Pelulus untuk keputusan', 15, '2026-01-08 03:55:37'),
(112, 30, 'Diluluskan oleh Pegawai Pelulus', 'Diluluskan oleh Pn. Alia Binti Mohd Yusof. Ulasan: boleh diluluskan', 16, '2026-01-08 03:57:06'),
(113, 27, 'Dalam Proses', 'Aduan telah disahkan oleh Unit Aduan Dalaman, ditugaskan kepada pegawai pelaksana, dan dimajukan kepada Unit Aset untuk tindakan selanjutnya', 14, '2026-01-08 03:58:13'),
(114, 27, 'Dimajukan ke Pegawai Pelulus', 'Borang Kerosakan Aset Alih telah disemak oleh Unit Aset dan dimajukan kepada Pegawai Pelulus untuk keputusan', 15, '2026-01-08 03:58:57'),
(115, 30, 'Selesai', 'Tindakan telah diselesaikan oleh Unit IT / Sokongan pada 08/01/2026. Catatan: selsesai', 18, '2026-01-08 04:32:20'),
(116, 27, 'Diluluskan oleh Pegawai Pelulus', 'Diluluskan oleh Pn. Alia Binti Mohd Yusof. Ulasan: diluluskan', 16, '2026-01-08 04:34:14'),
(117, 27, 'Selesai', 'Tindakan telah diselesaikan oleh Unit IT / Sokongan pada 08/01/2026. Catatan: telah selesai', 18, '2026-01-08 04:35:02');

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
(11, 25, 'ADU-2025-065', 1, 'Aduan telah diterima. sila semak', 'Aduan telah diterima. sila semak', '2025-12-30', 14, '2025-12-30 10:26:44', '2025-12-30 10:26:44'),
(13, 26, 'ADU-2025-280', 1, 'aduan telah diterima', 'aduan telah diterima', '2025-12-31', 14, '2025-12-31 02:04:32', '2025-12-31 02:04:32'),
(14, 28, 'ADU-2025-081', 1, 'Aduan telah diterima', 'Aduan telah diterima', '2025-12-31', 14, '2025-12-31 03:21:34', '2025-12-31 03:21:34'),
(15, 29, 'ADU-2026-186', 1, 'telah diterima', 'telah diterima', '2026-01-05', 14, '2026-01-05 06:56:39', '2026-01-05 06:56:39'),
(16, 31, 'ADU-2026-084', 1, 'aduan diterima', 'aduan diterima', '2026-01-07', 14, '2026-01-07 01:57:46', '2026-01-07 01:57:46'),
(17, 32, 'ADU-2026-724', 1, 'boleh dibaiki', 'boleh dibaiki', '2026-01-07', 14, '2026-01-07 02:00:44', '2026-01-07 02:00:44'),
(18, 33, 'ADU-2026-852', 1, 'diterima', 'diterima', '2026-01-08', 14, '2026-01-08 01:23:24', '2026-01-08 01:23:24'),
(19, 30, 'ADU-2026-803', 1, 'dimajukan ke unit aset', 'dimajukan ke unit aset', '2026-01-08', 14, '2026-01-08 03:54:29', '2026-01-08 03:54:29'),
(20, 27, 'ADU-2025-467', 1, 'dimajukan unit aset', 'dimajukan unit aset', '2026-01-08', 14, '2026-01-08 03:58:13', '2026-01-08 03:58:13');

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
(1, 'Pn. Maznah Binti Marzuki', 'maznah@jpbdselangor.gov.my', '03-12345678', 'Pembantu Tadbir', 'aktif', '2025-12-29 04:12:41', '2025-12-29 04:12:41');

-- --------------------------------------------------------

--
-- Table structure for table `unit_it_sokongan_officers`
--

CREATE TABLE `unit_it_sokongan_officers` (
  `id` int NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_telefon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jawatan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('aktif','tidak_aktif') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `unit_it_sokongan_officers`
--

INSERT INTO `unit_it_sokongan_officers` (`id`, `nama`, `email`, `no_telefon`, `jawatan`, `status`, `created_at`, `updated_at`) VALUES
(1, 'En. Mohd Faizal Bin Ahmad', 'faizal@jpbdselangor.gov.my', '03-12345681', 'Juruteknik Komputer', 'aktif', '2025-12-30 03:11:27', '2025-12-30 03:11:27');

-- --------------------------------------------------------

--
-- Table structure for table `unit_korporat_officers`
--

CREATE TABLE `unit_korporat_officers` (
  `id` int NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jawatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bahagian` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('aktif','tidak_aktif') COLLATE utf8mb4_unicode_ci DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `unit_korporat_officers`
--

INSERT INTO `unit_korporat_officers` (`id`, `nama`, `email`, `jawatan`, `bahagian`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Muhammad Adzhan bin Mohd Saike', 'adzhan@jpbdselangor.gov.my', 'Pegawai Unit Korporat', 'Unit Korporat (Laporan)', 'aktif', '2026-01-09 04:11:41', '2026-01-09 04:11:41');

-- --------------------------------------------------------

--
-- Table structure for table `unit_pentadbiran_officers`
--

CREATE TABLE `unit_pentadbiran_officers` (
  `id` int NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jawatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bahagian` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('aktif','tidak_aktif') COLLATE utf8mb4_unicode_ci DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `role` enum('user','admin','unit_aduan_dalaman','unit_aset','bahagian_pentadbiran_kewangan','unit_it_sokongan','unit_korporat','unit_pentadbiran','staff') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
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
(10, 'Naquib Haziq', 'naquib@jpbdselangor.gov.my', '$2y$10$XeEwJmOEx6XYdfZml4bIsO2ROmHQvwROpoBc.BH4YdGrZ97Gi1QcW', 'Pegawai Teknologi', 'perancangan', NULL, '1998', '3', 'user', 'active', '2025-12-04 04:52:43', '2025-12-04 04:52:43', NULL, NULL),
(14, 'En. Azri Hanis Bin Zul', 'azri.hanis@jpbdselangor.gov.my', '$2y$12$8Wos13KhnYKoWOttGDI28OSaxxrS/oAIhtcczMKtMY5rm0EytQF7i', 'Pegawai Unit Aduan Dalaman', 'Bahagian Pentadbiran dan Kewangan ', 'Unit Aduan Dalaman', NULL, NULL, 'unit_aduan_dalaman', 'active', '2025-12-29 06:32:32', '2025-12-30 07:06:18', NULL, NULL),
(15, 'Pn. Maznah Binti Marzuki', 'maznah@jpbdselangor.gov.my', '$2y$12$8Wos13KhnYKoWOttGDI28OSaxxrS/oAIhtcczMKtMY5rm0EytQF7i', 'Pembantu Tadbir', 'Bahagian Pentadbiran dan Kewangan', 'Unit Aset', NULL, NULL, 'unit_aset', 'active', '2025-12-29 06:32:32', '2025-12-30 07:07:13', NULL, NULL),
(16, 'Pn. Alia Binti Mohd Yusof', 'alia.yusof@jpbdselangor.gov.my', '$2y$12$8Wos13KhnYKoWOttGDI28OSaxxrS/oAIhtcczMKtMY5rm0EytQF7i', 'Penolong Pengarah', 'Bahagian Pentadbiran & Kewangan', 'Bahagian Pentadbiran & Kewangan', NULL, NULL, 'bahagian_pentadbiran_kewangan', 'active', '2025-12-29 06:32:32', '2025-12-29 06:32:32', NULL, NULL),
(17, 'Amar Maaruf', 'amar@jpbdselangor.gov.my', '$2y$10$Nzff3eHPKSj10PQO7flWquM.90YZAic/5h4jr0EahR/akQB5HfslS', 'Pegawai Teknologi Maklumat', 'pembangunan', NULL, '1335', '2', 'user', 'active', '2025-12-30 00:22:00', '2025-12-30 00:22:00', NULL, NULL),
(18, 'En. Mohd Faizal Bin Ahmad', 'faizal@jpbdselangor.gov.my', '$2y$12$8Wos13KhnYKoWOttGDI28OSaxxrS/oAIhtcczMKtMY5rm0EytQF7i', 'Juruteknik Komputer', 'Unit IT / Sokongan', 'Unit IT / Sokongan', '', NULL, 'unit_it_sokongan', 'active', '2025-12-30 03:11:27', '2025-12-30 16:36:52', NULL, NULL),
(19, 'Adam Idris', 'adam@jpbdselangor.gov.my', '$2y$10$MyCCKe6DDQAdJMMoaQ9.A.eqTEA58ZD7ZXEHmULSjSLLrALLdShgO', 'Pegawai Bandar Perancang', 'perancangan', NULL, '2005', '3', 'user', 'active', '2025-12-30 06:43:10', '2025-12-30 06:43:10', NULL, NULL),
(20, 'Adlin Nabila', 'adlin@jpbdselangor.gov.my', '$2y$10$3nOKlvOF67xbLfhtXlety.okUmf3/9Jpt.zV.WMwQblxo6UAANz7O', 'Pegawai Teknologi Maklumat', 'perancangan', NULL, '2001', '9', 'user', 'active', '2025-12-30 10:23:53', '2025-12-30 10:23:53', NULL, NULL),
(21, 'Syazwani Ili', 'wani@jpbdselangor.gov.my', '$2y$10$vHes/kkVHHIUexhoYvkDzuADgomJD8CSZgIv09GnEEe2tixcyWifq', 'Pegawai Bandar Perancang', 'korporat', NULL, '2007', '3', 'user', 'active', '2025-12-31 00:35:15', '2025-12-31 00:35:15', NULL, NULL),
(22, 'Alya Azuan', 'alya@jpbdselangor.gov.my', '$2y$10$IIq38jdXj8GbJGLdbT91quMZbrAIHDAWgXKpulgrZgTbRWy.QgGiu', 'Pegawai Bandar Perancang', 'perancangan', NULL, '2001', '3', 'user', 'active', '2025-12-31 03:13:06', '2025-12-31 03:13:06', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `role_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `assigned_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role_name`, `assigned_at`, `assigned_by`) VALUES
(1, 15, 'unit_aset', '2026-01-09 04:11:41', NULL),
(2, 15, 'unit_pentadbiran', '2026-01-09 04:11:41', NULL),
(3, 15, 'user', '2026-01-09 04:11:41', NULL);

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
(30, 25, 'forward_to_unit_aset', 'baru', 'dimajukan_unit_aset', 14, 'Aduan disahkan dan dimajukan ke Unit Aset', '2025-12-30 10:26:44'),
(31, 25, 'dalam_semakan_unit_aset', 'dimajukan_unit_aset', 'dalam_semakan_unit_aset', 15, 'Unit Aset sedang menyemak dan mengisi Borang Kerosakan Aset Alih', '2025-12-30 10:30:39'),
(32, 25, 'dimajukan_pegawai_pelulus', 'dalam_semakan_unit_aset', 'dimajukan_pegawai_pelulus', 15, 'Borang Kerosakan Aset Alih telah lengkap dan dimajukan kepada Pegawai Pelulus untuk keputusan', '2025-12-30 10:33:00'),
(33, 25, 'keputusan_pegawai_pelulus', 'dimajukan_pegawai_pelulus', 'dimajukan_unit_it', 16, 'Diluluskan oleh Pn. Alia Binti Mohd Yusof - diluluskan untuk tindakan selanjutnya', '2025-12-30 10:34:58'),
(34, 25, 'selesai', 'dimajukan_unit_it', 'selesai', 18, 'Tindakan selesai oleh Unit IT / Sokongan - telah selesai', '2025-12-30 10:36:40'),
(40, 26, 'forward_to_unit_aset', 'baru', 'dimajukan_unit_aset', 14, 'Aduan disahkan dan dimajukan ke Unit Aset', '2025-12-31 02:04:32'),
(41, 26, 'dalam_semakan_unit_aset', 'dimajukan_unit_aset', 'dalam_semakan_unit_aset', 15, 'Unit Aset sedang menyemak dan mengisi Borang Kerosakan Aset Alih', '2025-12-31 02:06:31'),
(42, 26, 'dimajukan_pegawai_pelulus', 'dalam_semakan_unit_aset', 'dimajukan_pegawai_pelulus', 15, 'Borang Kerosakan Aset Alih telah lengkap dan dimajukan kepada Pegawai Pelulus untuk keputusan', '2025-12-31 02:08:01'),
(43, 26, 'keputusan_pegawai_pelulus', 'dimajukan_pegawai_pelulus', 'dimajukan_unit_it', 16, 'Diluluskan oleh Pn. Alia Binti Mohd Yusof - aduan diluluskan untuk tindakan selanjutnya', '2025-12-31 02:10:21'),
(44, 26, 'selesai', 'dimajukan_unit_it', 'selesai', 18, 'Tindakan selesai oleh Unit IT / Sokongan - projektor telah diperbaiki', '2025-12-31 02:14:34'),
(45, 28, 'forward_to_unit_aset', 'baru', 'dimajukan_unit_aset', 14, 'Aduan disahkan dan dimajukan ke Unit Aset', '2025-12-31 03:21:34'),
(46, 28, 'dalam_semakan_unit_aset', 'dimajukan_unit_aset', 'dalam_semakan_unit_aset', 15, 'Unit Aset sedang menyemak dan mengisi Borang Kerosakan Aset Alih', '2025-12-31 03:26:06'),
(47, 28, 'dimajukan_pegawai_pelulus', 'dalam_semakan_unit_aset', 'dimajukan_pegawai_pelulus', 15, 'Borang Kerosakan Aset Alih telah lengkap dan dimajukan kepada Pegawai Pelulus untuk keputusan', '2025-12-31 03:27:24'),
(48, 28, 'keputusan_pegawai_pelulus', 'dimajukan_pegawai_pelulus', 'dimajukan_unit_it', 16, 'Diluluskan oleh Pn. Alia Binti Mohd Yusof - diluluskan', '2025-12-31 03:36:47'),
(49, 28, 'selesai', 'dimajukan_unit_it', 'selesai', 18, 'Tindakan selesai oleh Unit IT / Sokongan - selesai', '2025-12-31 03:50:25'),
(50, 29, 'forward_to_unit_aset', 'baru', 'dimajukan_unit_aset', 14, 'Aduan disahkan dan dimajukan ke Unit Aset', '2026-01-05 06:56:39'),
(51, 29, 'dimajukan_pegawai_pelulus', 'dimajukan_unit_aset', 'dimajukan_pegawai_pelulus', 15, 'Borang Kerosakan Aset Alih telah lengkap dan dimajukan kepada Pegawai Pelulus untuk keputusan', '2026-01-05 06:57:30'),
(52, 29, 'keputusan_pegawai_pelulus', 'dimajukan_pegawai_pelulus', 'dimajukan_unit_it', 16, 'Diluluskan oleh Pn. Alia Binti Mohd Yusof - diluluskan', '2026-01-05 06:58:57'),
(53, 29, 'selesai', 'dimajukan_unit_it', 'selesai', 18, 'Tindakan selesai oleh Unit IT / Sokongan - telah selesai laksanakan', '2026-01-05 06:59:42'),
(54, 31, 'forward_to_unit_aset', 'baru', 'dimajukan_unit_aset', 14, 'Aduan disahkan dan dimajukan ke Unit Aset', '2026-01-07 01:57:46'),
(55, 31, 'dimajukan_pegawai_pelulus', 'dimajukan_unit_aset', 'dimajukan_pegawai_pelulus', 15, 'Borang Kerosakan Aset Alih telah lengkap dan dimajukan kepada Pegawai Pelulus untuk keputusan', '2026-01-07 01:59:53'),
(56, 32, 'forward_to_unit_aset', 'baru', 'dimajukan_unit_aset', 14, 'Aduan disahkan dan dimajukan ke Unit Aset', '2026-01-07 02:00:44'),
(57, 31, 'keputusan_pegawai_pelulus', 'dimajukan_pegawai_pelulus', 'dimajukan_unit_it', 16, 'Diluluskan oleh Pn. Alia Binti Mohd Yusof - dibaiki', '2026-01-07 02:03:42'),
(58, 32, 'dimajukan_pegawai_pelulus', 'dimajukan_unit_aset', 'dimajukan_pegawai_pelulus', 15, 'Borang Kerosakan Aset Alih telah lengkap dan dimajukan kepada Pegawai Pelulus untuk keputusan', '2026-01-07 02:05:30'),
(59, 31, 'selesai', 'dimajukan_unit_it', 'selesai', 18, 'Tindakan selesai oleh Unit IT / Sokongan - selesai melaksana', '2026-01-07 02:06:40'),
(60, 33, 'forward_to_unit_aset', 'baru', 'dimajukan_unit_aset', 14, 'Aduan disahkan, dimajukan ke Unit Aset, dan ditugaskan kepada En. Mohd Faizal Bin Ahmad (Unit IT/Pentadbiran)', '2026-01-08 01:23:24'),
(61, 33, 'dimajukan_pegawai_pelulus', 'dimajukan_unit_aset', 'dimajukan_pegawai_pelulus', 15, 'Borang Kerosakan Aset Alih telah lengkap dan dimajukan kepada Pegawai Pelulus untuk keputusan', '2026-01-08 01:24:19'),
(62, 33, 'keputusan_pegawai_pelulus', 'dimajukan_pegawai_pelulus', 'diluluskan', 16, 'Diluluskan oleh Pn. Alia Binti Mohd Yusof - diluluskan', '2026-01-08 02:00:03'),
(63, 30, 'forward_to_unit_aset', 'baru', 'dimajukan_unit_aset', 14, 'Aduan disahkan, dimajukan ke Unit Aset, dan ditugaskan kepada En. Mohd Faizal Bin Ahmad (Unit IT/Pentadbiran)', '2026-01-08 03:54:29'),
(64, 30, 'dimajukan_pegawai_pelulus', 'dimajukan_unit_aset', 'dimajukan_pegawai_pelulus', 15, 'Borang Kerosakan Aset Alih telah lengkap dan dimajukan kepada Pegawai Pelulus untuk keputusan', '2026-01-08 03:55:37'),
(65, 30, 'keputusan_pegawai_pelulus', 'dimajukan_pegawai_pelulus', 'diluluskan', 16, 'Diluluskan oleh Pn. Alia Binti Mohd Yusof - boleh diluluskan', '2026-01-08 03:57:06'),
(66, 27, 'forward_to_unit_aset', 'baru', 'dimajukan_unit_aset', 14, 'Aduan disahkan, dimajukan ke Unit Aset, dan ditugaskan kepada En. Mohd Faizal Bin Ahmad (Unit IT/Pentadbiran)', '2026-01-08 03:58:13'),
(67, 27, 'dimajukan_pegawai_pelulus', 'dimajukan_unit_aset', 'dimajukan_pegawai_pelulus', 15, 'Borang Kerosakan Aset Alih telah lengkap dan dimajukan kepada Pegawai Pelulus untuk keputusan', '2026-01-08 03:58:57'),
(68, 30, 'selesai', 'diluluskan', 'selesai', 18, 'Tindakan selesai oleh Unit IT / Sokongan - selsesai', '2026-01-08 04:32:20'),
(69, 27, 'keputusan_pegawai_pelulus', 'dimajukan_pegawai_pelulus', 'diluluskan', 16, 'Diluluskan oleh Pn. Alia Binti Mohd Yusof - diluluskan', '2026-01-08 04:34:14'),
(70, 27, 'selesai', 'diluluskan', 'selesai', 18, 'Tindakan selesai oleh Unit IT / Sokongan - telah selesai', '2026-01-08 04:35:02');

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
  ADD KEY `fk_approval_officer` (`approval_officer_id`),
  ADD KEY `fk_unit_it_officer` (`unit_it_officer_id`),
  ADD KEY `fk_unit_it_completed_by` (`unit_it_completed_by`);

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
-- Indexes for table `unit_it_sokongan_officers`
--
ALTER TABLE `unit_it_sokongan_officers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `unit_korporat_officers`
--
ALTER TABLE `unit_korporat_officers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `unit_pentadbiran_officers`
--
ALTER TABLE `unit_pentadbiran_officers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email` (`email`),
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
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_role` (`user_id`,`role_name`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_role_name` (`role_name`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `borang_kerosakan_aset`
--
ALTER TABLE `borang_kerosakan_aset`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `complaint_status_history`
--
ALTER TABLE `complaint_status_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `dokumen_unit_aduan`
--
ALTER TABLE `dokumen_unit_aduan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
-- AUTO_INCREMENT for table `unit_it_sokongan_officers`
--
ALTER TABLE `unit_it_sokongan_officers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `unit_korporat_officers`
--
ALTER TABLE `unit_korporat_officers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `unit_pentadbiran_officers`
--
ALTER TABLE `unit_pentadbiran_officers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `workflow_actions`
--
ALTER TABLE `workflow_actions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

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
  ADD CONSTRAINT `fk_unit_aset_processed_by` FOREIGN KEY (`unit_aset_processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_unit_it_completed_by` FOREIGN KEY (`unit_it_completed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_unit_it_officer` FOREIGN KEY (`unit_it_officer_id`) REFERENCES `unit_it_sokongan_officers` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
