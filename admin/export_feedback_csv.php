<?php
/**
 * Admin - Export User Feedback to CSV
 */

require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.html?redirect=admin');
}

$db = getDB();

// Get date range from query parameters
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// Get completed complaints with feedback within date range
$stmt = $db->prepare("
    SELECT c.*, o.nama as officer_name, u.nama_penuh as user_name
    FROM complaints c
    LEFT JOIN officers o ON c.officer_id = o.id
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.status = 'selesai'
    AND c.rating IS NOT NULL
    AND DATE(c.created_at) BETWEEN ? AND ?
    ORDER BY c.completed_at DESC
");
$stmt->execute([$date_from, $date_to]);
$feedbacks = $stmt->fetchAll();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan_maklum_balas_' . $date_from . '_to_' . $date_to . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add CSV headers
fputcsv($output, [
    'No. Tiket',
    'Perkara',
    'Nama Pengadu',
    'Email',
    'Pegawai Bertugas',
    'Penilaian',
    'Skor',
    'Komen Maklum Balas',
    'Tarikh Dibuat',
    'Tarikh Selesai'
]);

// Add data rows
foreach ($feedbacks as $feedback) {
    // Map rating to Malay labels and scores
    $rating_data = [
        'cemerlang' => ['label' => 'Cemerlang', 'score' => 5],
        'baik' => ['label' => 'Baik', 'score' => 4],
        'memuaskan' => ['label' => 'Memuaskan', 'score' => 3],
        'tidak_memuaskan' => ['label' => 'Tidak Memuaskan', 'score' => 2]
    ];

    $rating_info = $rating_data[$feedback['rating']] ?? ['label' => 'N/A', 'score' => 0];

    fputcsv($output, [
        $feedback['ticket_number'],
        $feedback['perkara'],
        $feedback['nama_pengadu'],
        $feedback['email'],
        $feedback['officer_name'] ?? 'Belum ditugaskan',
        $rating_info['label'],
        $rating_info['score'],
        $feedback['feedback_comment'] ?? '',
        date('d/m/Y H:i', strtotime($feedback['created_at'])),
        !empty($feedback['completed_at']) ? date('d/m/Y H:i', strtotime($feedback['completed_at'])) : ''
    ]);
}

fclose($output);
exit;
