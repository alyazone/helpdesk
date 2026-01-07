<?php
/**
 * Unit Aduan Dalaman - Assign IT Officer
 * Handles assignment of Unit IT / Sokongan officer for approved complaints
 */

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isUnitAduanDalaman()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$db = getDB();
$user = getUser();

try {
    $complaint_id = intval($_POST['complaint_id'] ?? 0);
    $unit_it_officer_id = intval($_POST['unit_it_officer_id'] ?? 0);
    $tindakan_susulan = sanitize($_POST['tindakan_susulan'] ?? '');

    // Validation
    if ($complaint_id <= 0 || $unit_it_officer_id <= 0 || empty($tindakan_susulan)) {
        echo json_encode(['success' => false, 'message' => 'Sila lengkapkan semua medan yang diperlukan']);
        exit();
    }

    // Get complaint
    $stmt = $db->prepare("SELECT * FROM complaints WHERE id = ?");
    $stmt->execute([$complaint_id]);
    $complaint = $stmt->fetch();

    if (!$complaint) {
        echo json_encode(['success' => false, 'message' => 'Aduan tidak dijumpai']);
        exit();
    }

    // Check if complaint is approved
    if ($complaint['workflow_status'] !== 'diluluskan') {
        echo json_encode(['success' => false, 'message' => 'Aduan belum diluluskan. Status semasa: ' . $complaint['workflow_status']]);
        exit();
    }

    // Verify Unit IT officer exists
    $stmt = $db->prepare("SELECT * FROM unit_it_sokongan_officers WHERE id = ? AND status = 'aktif'");
    $stmt->execute([$unit_it_officer_id]);
    $officer = $stmt->fetch();

    if (!$officer) {
        echo json_encode(['success' => false, 'message' => 'Pegawai Unit IT tidak dijumpai atau tidak aktif']);
        exit();
    }

    // Begin transaction
    $db->beginTransaction();

    // Update complaint
    $stmt = $db->prepare("
        UPDATE complaints SET
            workflow_status = 'dimajukan_unit_it',
            unit_it_officer_id = ?,
            unit_it_assigned_at = NOW(),
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([
        $unit_it_officer_id,
        $complaint_id
    ]);

    // Update dokumen unit aduan
    $stmt = $db->prepare("
        UPDATE dokumen_unit_aduan SET
            tindakan_kesimpulan = ?,
            updated_at = NOW()
        WHERE complaint_id = ?
    ");
    $stmt->execute([
        $tindakan_susulan,
        $complaint_id
    ]);

    // Log workflow action
    $stmt = $db->prepare("
        INSERT INTO workflow_actions (complaint_id, action_type, from_status, to_status, performed_by, remarks)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $complaint_id,
        'assign_to_unit_it',
        'diluluskan',
        'dimajukan_unit_it',
        $user['id'],
        'Aduan dimajukan kepada ' . $officer['nama'] . ' di Unit IT / Sokongan'
    ]);

    // Add to complaint status history for public viewing
    $stmt = $db->prepare("
        INSERT INTO complaint_status_history (complaint_id, status, keterangan, created_by)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $complaint_id,
        'Dimajukan ke Unit IT / Sokongan',
        'Aduan telah dimajukan kepada Unit IT / Sokongan untuk tindakan selanjutnya',
        $user['id']
    ]);

    // Commit transaction
    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Aduan berjaya dimajukan ke Unit IT / Sokongan'
    ]);

} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Error assigning IT officer: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Ralat sistem: ' . $e->getMessage()
    ]);
}
