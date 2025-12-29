<?php
/**
 * Update Complaint Status API
 * Admin only - Updates complaint status and progress
 */

require_once __DIR__ . '/../config/config.php';

// Check admin authentication
if (!isLoggedIn() || !isAdmin()) {
    jsonResponse(false, 'Akses ditolak. Hanya admin boleh mengemas kini status');
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$complaint_id = (int)($data['complaint_id'] ?? 0);
$status = sanitize($data['status'] ?? '');
$progress = (int)($data['progress'] ?? 0);
$keterangan = sanitize($data['keterangan'] ?? '');

// Validation
if (empty($complaint_id)) {
    jsonResponse(false, 'ID aduan diperlukan');
}

if (empty($status)) {
    jsonResponse(false, 'Status diperlukan');
}

$allowed_statuses = ['pending', 'dalam_pemeriksaan', 'sedang_dibaiki', 'selesai', 'dibatalkan'];
if (!in_array($status, $allowed_statuses)) {
    jsonResponse(false, 'Status tidak sah');
}

if ($progress < 0 || $progress > 100) {
    jsonResponse(false, 'Progress mestilah antara 0-100');
}

try {
    $db = getDB();

    // Check if complaint exists
    $stmt = $db->prepare("SELECT id FROM complaints WHERE id = ?");
    $stmt->execute([$complaint_id]);

    if (!$stmt->fetch()) {
        jsonResponse(false, 'Aduan tidak dijumpai');
    }

    // Update complaint
    $update_fields = ['status = ?', 'progress = ?'];
    $params = [$status, $progress];

    // If status is completed, set completion date
    if ($status === 'selesai') {
        $update_fields[] = 'completed_at = NOW()';
    }

    $sql = "UPDATE complaints SET " . implode(', ', $update_fields) . " WHERE id = ?";
    $params[] = $complaint_id;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    // Add to status history
    $status_labels = [
        'pending' => 'Pending',
        'dalam_pemeriksaan' => 'Dalam Pemeriksaan',
        'sedang_dibaiki' => 'Sedang Dibaiki',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan'
    ];

    $status_label = $status_labels[$status] ?? $status;
    $history_keterangan = !empty($keterangan) ? $keterangan : "Status dikemas kini kepada: {$status_label}";

    $stmt = $db->prepare("
        INSERT INTO complaint_status_history (complaint_id, status, keterangan, created_by)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$complaint_id, $status_label, $history_keterangan, $_SESSION['user_id']]);

    jsonResponse(true, 'Status berjaya dikemas kini', [
        'complaint_id' => $complaint_id,
        'status' => $status,
        'progress' => $progress
    ]);

} catch (PDOException $e) {
    jsonResponse(false, 'Ralat sistem: ' . $e->getMessage());
}
