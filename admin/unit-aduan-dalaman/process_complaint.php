<?php
/**
 * Unit Aduan Dalaman - Process Complaint
 * Handles verification and forwarding to Unit Aset
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
    $dimajukan_ke = intval($_POST['dimajukan_ke'] ?? 0);
    $unit_it_officer_id = intval($_POST['unit_it_officer_id'] ?? 0);
    $tindakan_susulan = sanitize($_POST['tindakan_susulan'] ?? '');
    $no_rujukan_fail = sanitize($_POST['no_rujukan_fail'] ?? '');

    // Validation
    if ($complaint_id <= 0 || $dimajukan_ke <= 0 || $unit_it_officer_id <= 0 || empty($tindakan_susulan)) {
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

    // Check if complaint is in correct status
    if ($complaint['workflow_status'] !== 'baru' && $complaint['workflow_status'] !== 'disahkan_unit_aduan') {
        echo json_encode(['success' => false, 'message' => 'Status aduan tidak sesuai untuk diproses']);
        exit();
    }

    // Get Unit IT officer name for logging
    $stmt = $db->prepare("SELECT nama FROM unit_it_sokongan_officers WHERE id = ?");
    $stmt->execute([$unit_it_officer_id]);
    $unit_it_officer = $stmt->fetch();
    $unit_it_officer_name = $unit_it_officer ? $unit_it_officer['nama'] : 'Unknown';

    // Begin transaction
    $db->beginTransaction();

    // Update complaint
    $stmt = $db->prepare("
        UPDATE complaints SET
            workflow_status = 'dimajukan_unit_aset',
            unit_aduan_verified_by = ?,
            unit_aduan_verified_at = NOW(),
            dimajukan_ke = ?,
            tindakan_susulan = ?,
            unit_it_officer_id = ?,
            unit_it_assigned_at = NOW(),
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([
        $user['id'],
        $dimajukan_ke,
        $tindakan_susulan,
        $unit_it_officer_id,
        $complaint_id
    ]);

    // Create or update dokumen unit aduan
    $stmt = $db->prepare("
        INSERT INTO dokumen_unit_aduan (complaint_id, no_rujukan_fail, dimajukan_ke_officer_id, tindakan_susulan, tindakan_kesimpulan, tarikh, created_by)
        VALUES (?, ?, ?, ?, ?, CURDATE(), ?)
        ON DUPLICATE KEY UPDATE
            dimajukan_ke_officer_id = VALUES(dimajukan_ke_officer_id),
            tindakan_susulan = VALUES(tindakan_susulan),
            tindakan_kesimpulan = VALUES(tindakan_kesimpulan),
            tarikh = VALUES(tarikh),
            updated_at = NOW()
    ");
    $stmt->execute([
        $complaint_id,
        $no_rujukan_fail,
        $dimajukan_ke,
        $tindakan_susulan,
        $tindakan_susulan, // Using tindakan_susulan as tindakan_kesimpulan for now
        $user['id']
    ]);

    // Log workflow action
    $stmt = $db->prepare("
        INSERT INTO workflow_actions (complaint_id, action_type, from_status, to_status, performed_by, remarks)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $complaint_id,
        'forward_to_unit_aset',
        $complaint['workflow_status'],
        'dimajukan_unit_aset',
        $user['id'],
        'Aduan disahkan, dimajukan ke Unit Aset, dan ditugaskan kepada ' . $unit_it_officer_name . ' (Unit IT/Pentadbiran)'
    ]);

    // Add to complaint status history for public viewing
    $stmt = $db->prepare("
        INSERT INTO complaint_status_history (complaint_id, status, keterangan, created_by)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $complaint_id,
        'Dalam Proses',
        'Aduan telah disahkan oleh Unit Aduan Dalaman, ditugaskan kepada pegawai pelaksana, dan dimajukan kepada Unit Aset untuk tindakan selanjutnya',
        $user['id']
    ]);

    // Commit transaction
    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Aduan berjaya disahkan, ditugaskan kepada pegawai, dan dimajukan ke Unit Aset'
    ]);

} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Error processing complaint: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Ralat sistem: ' . $e->getMessage()
    ]);
}
