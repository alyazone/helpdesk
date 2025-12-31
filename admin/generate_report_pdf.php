<?php
/**
 * Admin - Generate PDF Report
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.html?redirect=admin');
}

// Get date range from query parameters
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

$db = getDB();

// Get complaints within date range
$stmt = $db->prepare("
    SELECT c.*, o.nama as officer_name
    FROM complaints c
    LEFT JOIN officers o ON c.officer_id = o.id
    WHERE DATE(c.created_at) BETWEEN ? AND ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$date_from, $date_to]);
$complaints = $stmt->fetchAll();

// Statistics by status
$stmt = $db->prepare("
    SELECT status, COUNT(*) as count
    FROM complaints
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY status
");
$stmt->execute([$date_from, $date_to]);
$status_stats = [];
foreach ($stmt->fetchAll() as $row) {
    $status_stats[$row['status']] = $row['count'];
}

// Statistics by jenis (type)
$stmt = $db->prepare("
    SELECT jenis, COUNT(*) as count
    FROM complaints
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY jenis
");
$stmt->execute([$date_from, $date_to]);
$jenis_stats = [];
foreach ($stmt->fetchAll() as $row) {
    $jenis_stats[$row['jenis']] = $row['count'];
}

// Top users by complaints submitted
$stmt = $db->prepare("
    SELECT u.nama_penuh as nama, COUNT(c.id) as total_complaints
    FROM users u
    LEFT JOIN complaints c ON u.id = c.user_id AND DATE(c.created_at) BETWEEN ? AND ?
    GROUP BY u.id, u.nama_penuh
    HAVING total_complaints > 0
    ORDER BY total_complaints DESC
    LIMIT 10
");
$stmt->execute([$date_from, $date_to]);
$top_users = $stmt->fetchAll();

// Average resolution time
$stmt = $db->prepare("
    SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_hours
    FROM complaints
    WHERE status = 'selesai'
    AND completed_at IS NOT NULL
    AND DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$date_from, $date_to]);
$avg_resolution = $stmt->fetch();
$avg_hours = round($avg_resolution['avg_hours'] ?? 0, 1);

// Daily complaints trend
$stmt = $db->prepare("
    SELECT DATE(created_at) as date, COUNT(*) as count
    FROM complaints
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute([$date_from, $date_to]);
$daily_trend = $stmt->fetchAll();

// Feedback statistics
$stmt = $db->prepare("
    SELECT rating, COUNT(*) as count
    FROM complaints
    WHERE status = 'selesai'
    AND rating IS NOT NULL
    AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY rating
    ORDER BY count DESC
");
$stmt->execute([$date_from, $date_to]);
$feedback_stats = [];
foreach ($stmt->fetchAll() as $row) {
    $feedback_stats[$row['rating']] = $row['count'];
}

// Total feedback summary
$stmt = $db->prepare("
    SELECT COUNT(*) as total,
           AVG(CASE
               WHEN rating = 'cemerlang' THEN 5
               WHEN rating = 'baik' THEN 4
               WHEN rating = 'memuaskan' THEN 3
               WHEN rating = 'tidak_memuaskan' THEN 2
               ELSE 0
           END) as overall_avg
    FROM complaints
    WHERE status = 'selesai'
    AND rating IS NOT NULL
    AND DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$date_from, $date_to]);
$feedback_summary = $stmt->fetch();
$total_feedback = $feedback_summary['total'];
$overall_avg_score = round($feedback_summary['overall_avg'] ?? 0, 2);

// Feedback with comments
$stmt = $db->prepare("
    SELECT COUNT(*) as total
    FROM complaints
    WHERE status = 'selesai'
    AND rating IS NOT NULL
    AND feedback_comment IS NOT NULL
    AND feedback_comment != ''
    AND DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$date_from, $date_to]);
$feedback_with_comments = $stmt->fetch()['total'];

// Create PDF
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 16);
        $this->Cell(0, 15, 'Laporan Analisis Aduan', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln();
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Helpdesk System');
$pdf->SetTitle('Laporan Analisis Aduan');
$pdf->SetSubject('Laporan Aduan');

// Set margins
$pdf->SetMargins(15, 30, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 20);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 10);

// Date Range
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Tempoh Laporan', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, 'Dari: ' . date('d/m/Y', strtotime($date_from)) . ' hingga ' . date('d/m/Y', strtotime($date_to)), 0, 1, 'L');
$pdf->Ln(5);

// Summary Statistics
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Ringkasan Statistik', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

// Summary table
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(90, 8, 'Jumlah Aduan', 1, 0, 'L', 1);
$pdf->Cell(90, 8, count($complaints), 1, 1, 'R', 1);
$pdf->Cell(90, 8, 'Selesai', 1, 0, 'L');
$pdf->Cell(90, 8, $status_stats['selesai'] ?? 0, 1, 1, 'R');
$pdf->Cell(90, 8, 'Dalam Proses', 1, 0, 'L', 1);
$in_progress = ($status_stats['dalam_pemeriksaan'] ?? 0) + ($status_stats['sedang_dibaiki'] ?? 0);
$pdf->Cell(90, 8, $in_progress, 1, 1, 'R', 1);
$pdf->Cell(90, 8, 'Purata Masa Selesai (Jam)', 1, 0, 'L');
$pdf->Cell(90, 8, $avg_hours . 'h', 1, 1, 'R');
$pdf->Ln(5);

// Feedback Statistics
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Statistik Maklum Balas', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(90, 8, 'Jumlah Maklum Balas', 1, 0, 'L', 1);
$pdf->Cell(90, 8, $total_feedback, 1, 1, 'R', 1);
$pdf->Cell(90, 8, 'Purata Skor', 1, 0, 'L');
$pdf->Cell(90, 8, $overall_avg_score . '/5', 1, 1, 'R');
$pdf->Cell(90, 8, 'Dengan Komen', 1, 0, 'L', 1);
$pdf->Cell(90, 8, $feedback_with_comments, 1, 1, 'R', 1);
$pdf->Ln(5);

// Status Breakdown
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Pecahan Status', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

$pdf->SetFillColor(220, 220, 220);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(90, 8, 'Status', 1, 0, 'L', 1);
$pdf->Cell(90, 8, 'Jumlah', 1, 1, 'C', 1);
$pdf->SetFont('helvetica', '', 10);

$status_labels = [
    'pending' => 'Pending',
    'dalam_pemeriksaan' => 'Dalam Pemeriksaan',
    'sedang_dibaiki' => 'Sedang Dibaiki',
    'selesai' => 'Selesai',
    'dibatalkan' => 'Dibatalkan'
];

foreach ($status_labels as $key => $label) {
    $pdf->Cell(90, 7, $label, 1, 0, 'L');
    $pdf->Cell(90, 7, $status_stats[$key] ?? 0, 1, 1, 'C');
}
$pdf->Ln(5);

// Jenis Breakdown
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Jenis Aduan', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

$pdf->SetFillColor(220, 220, 220);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(90, 8, 'Jenis', 1, 0, 'L', 1);
$pdf->Cell(90, 8, 'Jumlah', 1, 1, 'C', 1);
$pdf->SetFont('helvetica', '', 10);

$pdf->Cell(90, 7, 'Aduan', 1, 0, 'L');
$pdf->Cell(90, 7, $jenis_stats['aduan'] ?? 0, 1, 1, 'C');
$pdf->Cell(90, 7, 'Cadangan', 1, 0, 'L');
$pdf->Cell(90, 7, $jenis_stats['cadangan'] ?? 0, 1, 1, 'C');
$pdf->Ln(5);

// Feedback Distribution
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Taburan Maklum Balas', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

$pdf->SetFillColor(220, 220, 220);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(90, 8, 'Rating', 1, 0, 'L', 1);
$pdf->Cell(90, 8, 'Jumlah', 1, 1, 'C', 1);
$pdf->SetFont('helvetica', '', 10);

$rating_labels = [
    'cemerlang' => 'Cemerlang',
    'baik' => 'Baik',
    'memuaskan' => 'Memuaskan',
    'tidak_memuaskan' => 'Tidak Memuaskan'
];

foreach ($rating_labels as $key => $label) {
    $pdf->Cell(90, 7, $label, 1, 0, 'L');
    $pdf->Cell(90, 7, $feedback_stats[$key] ?? 0, 1, 1, 'C');
}
$pdf->Ln(5);

// Top Users
if (!empty($top_users)) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Pengguna Teraktif (Top 10)', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);

    $pdf->SetFillColor(220, 220, 220);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(10, 8, 'No', 1, 0, 'C', 1);
    $pdf->Cell(120, 8, 'Nama', 1, 0, 'L', 1);
    $pdf->Cell(50, 8, 'Jumlah Aduan', 1, 1, 'C', 1);
    $pdf->SetFont('helvetica', '', 10);

    $no = 1;
    foreach ($top_users as $user) {
        $pdf->Cell(10, 7, $no++, 1, 0, 'C');
        $pdf->Cell(120, 7, $user['nama'], 1, 0, 'L');
        $pdf->Cell(50, 7, $user['total_complaints'], 1, 1, 'C');
    }
    $pdf->Ln(5);
}

// Daily Trend - Add new page if needed
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Trend Harian', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

if (!empty($daily_trend)) {
    $pdf->SetFillColor(220, 220, 220);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(90, 8, 'Tarikh', 1, 0, 'L', 1);
    $pdf->Cell(90, 8, 'Jumlah Aduan', 1, 1, 'C', 1);
    $pdf->SetFont('helvetica', '', 10);

    foreach ($daily_trend as $day) {
        $pdf->Cell(90, 7, date('d/m/Y', strtotime($day['date'])), 1, 0, 'L');
        $pdf->Cell(90, 7, $day['count'], 1, 1, 'C');
    }
} else {
    $pdf->Cell(0, 8, 'Tiada data untuk tempoh ini.', 0, 1, 'L');
}

// Output PDF
$filename = 'Laporan_Aduan_' . date('Ymd', strtotime($date_from)) . '_' . date('Ymd', strtotime($date_to)) . '.pdf';
$pdf->Output($filename, 'D');
