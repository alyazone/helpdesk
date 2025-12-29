<?php
/**
 * Get Complaint Details API
 * Retrieves detailed information about a specific complaint
 */

require_once __DIR__ . '/../config/config.php';

// Accept both GET and POST requests
$complaint_id = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $complaint_id = (int)($data['complaint_id'] ?? 0);
} else {
    $complaint_id = (int)($_GET['complaint_id'] ?? 0);
}

// Validation
if (empty($complaint_id)) {
    jsonResponse(false, 'ID aduan diperlukan');
}

try {
    $db = getDB();

    // Get complaint details
    $stmt = $db->prepare("
        SELECT
            c.*,
            o.nama as officer_name,
            o.email as officer_email
        FROM complaints c
        LEFT JOIN officers o ON c.officer_id = o.id
        WHERE c.id = ?
    ");
    $stmt->execute([$complaint_id]);
    $complaint = $stmt->fetch();

    if (!$complaint) {
        jsonResponse(false, 'Aduan tidak dijumpai');
    }

    // Get status history
    $stmt = $db->prepare("
        SELECT
            sh.*,
            u.nama_penuh as updated_by_name
        FROM complaint_status_history sh
        LEFT JOIN users u ON sh.created_by = u.id
        WHERE sh.complaint_id = ?
        ORDER BY sh.created_at ASC
    ");
    $stmt->execute([$complaint_id]);
    $status_history = $stmt->fetchAll();

    // Get attachments
    $stmt = $db->prepare("
        SELECT * FROM attachments
        WHERE complaint_id = ?
        ORDER BY uploaded_at ASC
    ");
    $stmt->execute([$complaint_id]);
    $attachments = $stmt->fetchAll();

    $complaint['status_history'] = $status_history;
    $complaint['attachments'] = $attachments;

    jsonResponse(true, 'Data berjaya diambil', [
        'complaint' => $complaint
    ]);

} catch (PDOException $e) {
    jsonResponse(false, 'Ralat sistem: ' . $e->getMessage());
}
