<?php
/**
 * Bahagian Pentadbiran & Kewangan - Process Approval
 * Handles approval/rejection decision for Borang Kerosakan Aset Alih
 */

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isBahagianPentadbiranKewangan()) {
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
    if ($complaint['workflow_status'] !== 'dimajukan_pegawai_pelulus') {
        throw new Exception('Status aduan tidak sesuai untuk tindakan ini. Status semasa: ' . $complaint['workflow_status']);
    }

    // Validate required fields
    $keputusan_status = sanitize($_POST['keputusan_status'] ?? '');
    $keputusan_ulasan = sanitize($_POST['keputusan_ulasan'] ?? '');
    $keputusan_nama = sanitize($_POST['keputusan_nama'] ?? '');
    $keputusan_jawatan = sanitize($_POST['keputusan_jawatan'] ?? '');
    $keputusan_tarikh = sanitize($_POST['keputusan_tarikh'] ?? '');

    if (!in_array($keputusan_status, ['diluluskan', 'ditolak'])) {
        throw new Exception('Status keputusan tidak sah');
    }

    if (empty($keputusan_ulasan) || empty($keputusan_nama) || empty($keputusan_jawatan) || empty($keputusan_tarikh)) {
        throw new Exception('Sila lengkapkan semua maklumat yang diperlukan');
    }

    // Get borang kerosakan aset
    $stmt = $db->prepare("SELECT id FROM borang_kerosakan_aset WHERE complaint_id = ?");
    $stmt->execute([$complaint_id]);
    $borang = $stmt->fetch();

    if (!$borang) {
        throw new Exception('Borang Kerosakan Aset tidak dijumpai');
    }

    // Start transaction
    $db->beginTransaction();

    // Update Borang Kerosakan Aset - Bahagian III
    $stmt = $db->prepare("
        UPDATE borang_kerosakan_aset SET
            keputusan_status = ?,
            keputusan_ulasan = ?,
            keputusan_nama = ?,
            keputusan_jawatan = ?,
            keputusan_tarikh = ?,
            tandatangan_dijana_komputer = TRUE,
            updated_at = CURRENT_TIMESTAMP
        WHERE complaint_id = ?
    ");
    $stmt->execute([
        $keputusan_status,
        $keputusan_ulasan,
        $keputusan_nama,
        $keputusan_jawatan,
        $keputusan_tarikh,
        $complaint_id
    ]);

    // Update complaint workflow status
    $new_workflow_status = $keputusan_status; // 'diluluskan' or 'ditolak'

    $stmt = $db->prepare("
        UPDATE complaints SET
            workflow_status = ?,
            pegawai_pelulus_id = ?,
            pegawai_pelulus_reviewed_at = CURRENT_TIMESTAMP,
            pegawai_pelulus_status = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    $stmt->execute([$new_workflow_status, $user['id'], $keputusan_status, $complaint_id]);

    // Log workflow action
    $action_label = $keputusan_status === 'diluluskan' ? 'Diluluskan' : 'Ditolak';
    $stmt = $db->prepare("
        INSERT INTO workflow_actions (
            complaint_id, action_type, from_status, to_status, performed_by, remarks
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $complaint_id,
        'keputusan_pegawai_pelulus',
        $complaint['workflow_status'],
        $new_workflow_status,
        $user['id'],
        $action_label . ' oleh ' . $keputusan_nama . ' - ' . $keputusan_ulasan
    ]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Keputusan berjaya dihantar',
        'keputusan' => $keputusan_status,
        'workflow_status' => $new_workflow_status
    ]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
