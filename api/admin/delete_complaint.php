<?php
/**
 * API - Delete Complaint (Admin Only)
 */

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    jsonResponse(false, 'Akses ditolak');
}

$db = getDB();

try {
    $complaint_id = intval($_POST['complaint_id'] ?? 0);

    if ($complaint_id <= 0) {
        jsonResponse(false, 'ID aduan tidak sah');
    }

    // Check if complaint exists
    $stmt = $db->prepare("SELECT * FROM complaints WHERE id = ?");
    $stmt->execute([$complaint_id]);
    $complaint = $stmt->fetch();

    if (!$complaint) {
        jsonResponse(false, 'Aduan tidak dijumpai');
    }

    // Start transaction
    $db->beginTransaction();

    // Delete related records first (to maintain referential integrity)

    // 1. Delete attachments
    $stmt = $db->prepare("DELETE FROM attachments WHERE complaint_id = ?");
    $stmt->execute([$complaint_id]);

    // 2. Delete status history
    $stmt = $db->prepare("DELETE FROM complaint_status_history WHERE complaint_id = ?");
    $stmt->execute([$complaint_id]);

    // 3. Delete notifications related to this complaint
    $stmt = $db->prepare("DELETE FROM notifications WHERE complaint_id = ?");
    $stmt->execute([$complaint_id]);

    // 4. Delete workflow actions
    $stmt = $db->prepare("DELETE FROM workflow_actions WHERE complaint_id = ?");
    $stmt->execute([$complaint_id]);

    // 5. Delete borang kerosakan aset (if exists)
    $stmt = $db->prepare("DELETE FROM borang_kerosakan_aset WHERE complaint_id = ?");
    $stmt->execute([$complaint_id]);

    // 6. Delete dokumen unit aduan (if exists)
    $stmt = $db->prepare("DELETE FROM dokumen_unit_aduan WHERE complaint_id = ?");
    $stmt->execute([$complaint_id]);

    // Finally, delete the complaint itself
    $stmt = $db->prepare("DELETE FROM complaints WHERE id = ?");
    $stmt->execute([$complaint_id]);

    // Commit transaction
    $db->commit();

    jsonResponse(true, "Aduan #{$complaint['ticket_number']} berjaya dipadam");

} catch (Exception $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    jsonResponse(false, 'Ralat: ' . $e->getMessage());
}
