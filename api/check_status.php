<?php
/**
 * Check Status API
 * Retrieves complaint status by email
 */

require_once __DIR__ . '/../config/config.php';

// Accept both GET and POST requests
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = sanitize($data['email'] ?? '');
} else {
    $email = sanitize($_GET['email'] ?? '');
}

// Validation
if (empty($email)) {
    jsonResponse(false, 'Alamat emel diperlukan');
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Format alamat emel tidak sah');
}

try {
    $db = getDB();

    // Get all complaints for this email
    $stmt = $db->prepare("
        SELECT
            c.*,
            o.nama as officer_name,
            (SELECT COUNT(*) FROM attachments WHERE complaint_id = c.id) as attachment_count
        FROM complaints c
        LEFT JOIN officers o ON c.officer_id = o.id
        WHERE c.email = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$email]);
    $complaints = $stmt->fetchAll();

    if (empty($complaints)) {
        jsonResponse(false, 'Tiada rekod aduan dijumpai untuk alamat emel ini');
    }

    // Get statistics
    $stats = [
        'total' => count($complaints),
        'pending' => 0,
        'dalam_pemeriksaan' => 0,
        'sedang_dibaiki' => 0,
        'selesai' => 0,
        'dibatalkan' => 0
    ];

    foreach ($complaints as $complaint) {
        if (isset($stats[$complaint['status']])) {
            $stats[$complaint['status']]++;
        }
    }

    // Get status history for each complaint
    $complaints_with_history = [];
    foreach ($complaints as $complaint) {
        $stmt = $db->prepare("
            SELECT * FROM complaint_status_history
            WHERE complaint_id = ?
            ORDER BY created_at ASC
        ");
        $stmt->execute([$complaint['id']]);
        $history = $stmt->fetchAll();

        $complaint['status_history'] = $history;
        $complaints_with_history[] = $complaint;
    }

    jsonResponse(true, 'Data berjaya diambil', [
        'user' => [
            'email' => $email,
            'nama' => $complaints[0]['nama_pengadu'] ?? ''
        ],
        'statistics' => $stats,
        'complaints' => $complaints_with_history
    ]);

} catch (PDOException $e) {
    jsonResponse(false, 'Ralat sistem: ' . $e->getMessage());
}
