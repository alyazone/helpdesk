<?php
/**
 * Submit Rating API
 * Handles complaint rating/feedback submission
 */

// Disable error display to prevent breaking JSON response
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../config/config.php';

// Set proper headers
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Check if JSON decode was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('JSON decode error: ' . json_last_error_msg());
    error_log('Raw input: ' . $input);
    jsonResponse(false, 'Ralat: Data tidak sah');
}

// Validate that data is an array
if (!is_array($data)) {
    error_log('Data is not an array: ' . print_r($data, true));
    jsonResponse(false, 'Ralat: Format data tidak sah');
}

$complaint_id = isset($data['complaint_id']) ? (int)$data['complaint_id'] : 0;
$rating = isset($data['rating']) ? sanitize($data['rating']) : '';
$feedback_comment = isset($data['feedback_comment']) ? sanitize($data['feedback_comment']) : '';

// Log received data for debugging
error_log('Rating submission - Complaint ID: ' . $complaint_id . ', Rating: ' . $rating);

// Validation
if (empty($complaint_id)) {
    jsonResponse(false, 'ID aduan diperlukan');
}

if (empty($rating)) {
    jsonResponse(false, 'Penilaian diperlukan');
}

$allowed_ratings = ['cemerlang', 'baik', 'memuaskan', 'tidak_memuaskan'];
if (!in_array($rating, $allowed_ratings)) {
    error_log('Invalid rating received: ' . $rating);
    jsonResponse(false, 'Penilaian tidak sah. Sila pilih salah satu daripada: cemerlang, baik, memuaskan, atau tidak_memuaskan');
}

try {
    $db = getDB();

    // Check if complaint exists and is completed
    $stmt = $db->prepare("SELECT id, status, rating FROM complaints WHERE id = ?");
    $stmt->execute([$complaint_id]);
    $complaint = $stmt->fetch();

    if (!$complaint) {
        error_log('Complaint not found: ' . $complaint_id);
        jsonResponse(false, 'Aduan tidak dijumpai');
    }

    if ($complaint['status'] !== 'selesai') {
        error_log('Complaint not completed yet. Status: ' . $complaint['status']);
        jsonResponse(false, 'Hanya aduan yang telah selesai boleh diberi penilaian');
    }

    // Check if already rated
    if (!empty($complaint['rating'])) {
        error_log('Complaint already rated: ' . $complaint['rating']);
        // Allow re-rating, just log it
    }

    // Update complaint with rating
    $stmt = $db->prepare("
        UPDATE complaints
        SET rating = ?, feedback_comment = ?, updated_at = NOW()
        WHERE id = ?
    ");

    $result = $stmt->execute([$rating, $feedback_comment, $complaint_id]);

    if (!$result) {
        error_log('Failed to update rating for complaint: ' . $complaint_id);
        jsonResponse(false, 'Gagal menyimpan penilaian. Sila cuba lagi.');
    }

    error_log('Rating submitted successfully for complaint ' . $complaint_id . ': ' . $rating);

    jsonResponse(true, 'Terima kasih atas penilaian anda', [
        'complaint_id' => $complaint_id,
        'rating' => $rating
    ]);

} catch (PDOException $e) {
    error_log('Database error in submit_rating.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    jsonResponse(false, 'Ralat sistem. Sila cuba lagi.');
} catch (Exception $e) {
    error_log('Unexpected error in submit_rating.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    jsonResponse(false, 'Ralat tidak dijangka. Sila cuba lagi.');
}
