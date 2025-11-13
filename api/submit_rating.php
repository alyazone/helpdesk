<?php
/**
 * Submit Rating API
 * Handles complaint rating/feedback submission
 */

require_once __DIR__ . '/../config/config.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$complaint_id = (int)($data['complaint_id'] ?? 0);
$rating = sanitize($data['rating'] ?? '');
$feedback_comment = sanitize($data['feedback_comment'] ?? '');

// Validation
if (empty($complaint_id)) {
    jsonResponse(false, 'ID aduan diperlukan');
}

if (empty($rating)) {
    jsonResponse(false, 'Penilaian diperlukan');
}

$allowed_ratings = ['cemerlang', 'baik', 'memuaskan', 'tidak_memuaskan'];
if (!in_array($rating, $allowed_ratings)) {
    jsonResponse(false, 'Penilaian tidak sah');
}

try {
    $db = getDB();

    // Check if complaint exists and is completed
    $stmt = $db->prepare("SELECT id, status FROM complaints WHERE id = ?");
    $stmt->execute([$complaint_id]);
    $complaint = $stmt->fetch();

    if (!$complaint) {
        jsonResponse(false, 'Aduan tidak dijumpai');
    }

    if ($complaint['status'] !== 'selesai') {
        jsonResponse(false, 'Hanya aduan yang telah selesai boleh diberi penilaian');
    }

    // Update complaint with rating
    $stmt = $db->prepare("
        UPDATE complaints
        SET rating = ?, feedback_comment = ?
        WHERE id = ?
    ");
    $stmt->execute([$rating, $feedback_comment, $complaint_id]);

    jsonResponse(true, 'Terima kasih atas penilaian anda', [
        'complaint_id' => $complaint_id,
        'rating' => $rating
    ]);

} catch (PDOException $e) {
    jsonResponse(false, 'Ralat sistem: ' . $e->getMessage());
}
