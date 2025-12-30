<?php
/**
 * Unit IT / Sokongan - Process Completion
 * Handles marking complaint as selesai (completed)
 */

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isUnitITSokongan()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = getDB();
$user = getUser();

try {
    $complaint_id = intval($_POST['complaint_id'] ?? 0);

    if ($complaint_id <= 0) {
        throw new Exception('ID aduan tidak sah');
    }

    // Get complaint
    $stmt = $db->prepare("SELECT * FROM complaints WHERE id = ?");
    $stmt->execute([$complaint_id]);
    $complaint = $stmt->fetch();

    if (!$complaint) {
        throw new Exception('Aduan tidak dijumpai');
    }

    // Check if complaint is in correct workflow status
    if ($complaint['workflow_status'] !== 'dimajukan_unit_it') {
        throw new Exception('Status aduan tidak sesuai untuk tindakan ini. Status semasa: ' . $complaint['workflow_status']);
    }

    // Validate required fields
    $catatan = sanitize($_POST['catatan'] ?? '');
    $tarikh_selesai = sanitize($_POST['tarikh_selesai'] ?? '');

    if (empty($catatan)) {
        throw new Exception('Sila masukkan catatan tindakan yang telah diambil');
    }

    if (empty($tarikh_selesai)) {
        throw new Exception('Sila masukkan tarikh selesai');
    }

    // Start transaction
    $db->beginTransaction();

    // Update complaint workflow status
    $stmt = $db->prepare("
        UPDATE complaints SET
            workflow_status = 'selesai',
            status = 'selesai',
            unit_it_completed_by = ?,
            unit_it_completed_at = CURRENT_TIMESTAMP,
            completed_at = CURRENT_TIMESTAMP,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    $stmt->execute([$user['id'], $complaint_id]);

    // Log workflow action
    $stmt = $db->prepare("
        INSERT INTO workflow_actions (
            complaint_id, action_type, from_status, to_status, performed_by, remarks
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $complaint_id,
        'selesai',
        'dimajukan_unit_it',
        'selesai',
        $user['id'],
        'Tindakan selesai oleh Unit IT / Sokongan - ' . $catatan
    ]);

    // Add to complaint status history for public viewing
    $stmt = $db->prepare("
        INSERT INTO complaint_status_history (complaint_id, status, keterangan, created_by)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $complaint_id,
        'Selesai',
        'Tindakan telah diselesaikan oleh Unit IT / Sokongan pada ' . date('d/m/Y', strtotime($tarikh_selesai)) . '. Catatan: ' . $catatan,
        $user['id']
    ]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Aduan berjaya ditandakan sebagai selesai',
        'workflow_status' => 'selesai'
    ]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
