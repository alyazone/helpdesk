<?php
/**
 * Check Status API
 * Retrieves complaint status by email
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Calculate progress based on workflow status or regular status
 * @param string $workflow_status The workflow status
 * @param string $status The regular status (fallback)
 * @return int Progress percentage (0-100)
 */
function calculateProgress($workflow_status, $status) {
    // Workflow status progress mapping
    $workflow_progress = [
        'baru' => 10,
        'disahkan_unit_aduan' => 20,
        'dimajukan_unit_aset' => 30,
        'dalam_semakan_unit_aset' => 50,
        'dimajukan_pegawai_pelulus' => 70,
        'diluluskan' => 85,
        'ditolak' => 0,
        'selesai' => 100
    ];

    // Regular status progress mapping (fallback)
    $status_progress = [
        'pending' => 10,
        'dalam_pemeriksaan' => 30,
        'sedang_dibaiki' => 60,
        'selesai' => 100,
        'dibatalkan' => 0
    ];

    // Use workflow_status if available, otherwise use regular status
    if (!empty($workflow_status) && isset($workflow_progress[$workflow_status])) {
        return $workflow_progress[$workflow_status];
    } elseif (!empty($status) && isset($status_progress[$status])) {
        return $status_progress[$status];
    }

    // Default to 0 if no match
    return 0;
}

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

        // Get the latest status from status_history or workflow_status
        if (!empty($history)) {
            $latest_status = end($history)['status'];
            $complaint['current_status_label'] = $latest_status;
        } else {
            // Fallback to workflow_status if available
            $complaint['current_status_label'] = $complaint['workflow_status'] ?? $complaint['status'];
        }

        // Calculate dynamic progress based on workflow_status or status
        $complaint['progress'] = calculateProgress(
            $complaint['workflow_status'] ?? '',
            $complaint['status'] ?? ''
        );

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
