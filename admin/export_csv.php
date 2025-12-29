<?php
/**
 * Admin - Export Complaints to CSV
 */

require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.html?redirect=admin');
}

$db = getDB();

// Get date range from query parameters
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// Get complaints within date range
$stmt = $db->prepare("
    SELECT c.*, o.nama as officer_name, u.nama_penuh as user_name
    FROM complaints c
    LEFT JOIN officers o ON c.officer_id = o.id
    LEFT JOIN users u ON c.user_id = u.id
    WHERE DATE(c.created_at) BETWEEN ? AND ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$date_from, $date_to]);
$complaints = $stmt->fetchAll();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan_aduan_' . $date_from . '_to_' . $date_to . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add CSV headers
fputcsv($output, [
    'No. Tiket',
    'Jenis',
    'Perkara',
    'Keterangan',
    'Nama Pengadu',
    'Email',
    'No. Telefon',
    'Jawatan',
    'Bahagian',
    'Tingkat',
    'Status',
    'Keutamaan',
    'Kemajuan (%)',
    'Pegawai Bertugas',
    'Jenis Aset',
    'No. Pendaftaran Aset',
    'Penilaian',
    'Tarikh Dibuat',
    'Tarikh Kemaskini',
    'Tarikh Selesai'
]);

// Add data rows
foreach ($complaints as $complaint) {
    // Map status to Malay labels
    $status_labels = [
        'pending' => 'Pending',
        'dalam_pemeriksaan' => 'Dalam Pemeriksaan',
        'sedang_dibaiki' => 'Sedang Dibaiki',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan'
    ];

    // Map priority to Malay labels
    $priority_labels = [
        'rendah' => 'Rendah',
        'sederhana' => 'Sederhana',
        'tinggi' => 'Tinggi',
        'kritikal' => 'Kritikal'
    ];

    // Map rating to Malay labels
    $rating_labels = [
        'cemerlang' => 'Cemerlang',
        'baik' => 'Baik',
        'memuaskan' => 'Memuaskan',
        'tidak_memuaskan' => 'Tidak Memuaskan'
    ];

    fputcsv($output, [
        $complaint['ticket_number'],
        ucfirst($complaint['jenis']),
        $complaint['perkara'],
        $complaint['keterangan'],
        $complaint['nama_pengadu'],
        $complaint['email'],
        $complaint['no_telefon'] ?? '',
        $complaint['jawatan'] ?? '',
        $complaint['bahagian'] ?? '',
        $complaint['tingkat'] ?? '',
        $status_labels[$complaint['status']] ?? $complaint['status'],
        $priority_labels[$complaint['priority']] ?? $complaint['priority'],
        $complaint['progress'],
        $complaint['officer_name'] ?? 'Belum ditugaskan',
        $complaint['jenis_aset'] ?? '',
        $complaint['no_pendaftaran_aset'] ?? '',
        !empty($complaint['rating']) ? $rating_labels[$complaint['rating']] : '',
        date('d/m/Y H:i', strtotime($complaint['created_at'])),
        date('d/m/Y H:i', strtotime($complaint['updated_at'])),
        !empty($complaint['completed_at']) ? date('d/m/Y H:i', strtotime($complaint['completed_at'])) : ''
    ]);
}

fclose($output);
exit;
