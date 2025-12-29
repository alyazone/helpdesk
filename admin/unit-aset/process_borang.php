<?php
/**
 * Unit Aset - Process Borang Kerosakan Aset Alih
 * Handles form submission and forwarding to Pegawai Pelulus
 */

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isUnitAset()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = getDB();
$user = getUser();

try {
    $complaint_id = intval($_POST['complaint_id'] ?? 0);
    $action = sanitize($_POST['action'] ?? '');

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
    if (!in_array($complaint['workflow_status'], ['dimajukan_unit_aset', 'dalam_semakan_unit_aset'])) {
        throw new Exception('Status aduan tidak sesuai untuk tindakan ini');
    }

    // Validate required fields
    $anggaran_kos = floatval($_POST['anggaran_kos_penyelenggaraan'] ?? 0);
    $syor_ulasan = sanitize($_POST['syor_ulasan'] ?? '');
    $nama_pegawai = sanitize($_POST['nama_jawatan_pegawai_aset'] ?? '');
    $tarikh_pegawai = sanitize($_POST['tarikh_pegawai_aset'] ?? '');

    if (empty($syor_ulasan) || empty($nama_pegawai) || empty($tarikh_pegawai)) {
        throw new Exception('Sila lengkapkan semua maklumat yang diperlukan');
    }

    // Get optional fields
    $jumlah_kos_terdahulu = floatval($_POST['jumlah_kos_penyelenggaraan_terdahulu'] ?? 0);

    // Check if borang already exists
    $stmt = $db->prepare("SELECT id FROM borang_kerosakan_aset WHERE complaint_id = ?");
    $stmt->execute([$complaint_id]);
    $existing_borang = $stmt->fetch();

    // Start transaction
    $db->beginTransaction();

    if ($existing_borang) {
        // Update existing borang
        $stmt = $db->prepare("
            UPDATE borang_kerosakan_aset SET
                jumlah_kos_penyelenggaraan_terdahulu = ?,
                anggaran_kos_penyelenggaraan = ?,
                nama_jawatan_pegawai_aset = ?,
                tarikh_pegawai_aset = ?,
                syor_ulasan = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE complaint_id = ?
        ");
        $stmt->execute([
            $jumlah_kos_terdahulu,
            $anggaran_kos,
            $nama_pegawai,
            $tarikh_pegawai,
            $syor_ulasan,
            $complaint_id
        ]);
        $borang_id = $existing_borang['id'];
    } else {
        // Create new borang with Section I data from complaint
        $stmt = $db->prepare("
            INSERT INTO borang_kerosakan_aset (
                complaint_id,
                jenis_aset,
                no_siri_pendaftaran_aset,
                pengguna_terakhir,
                tarikh_kerosakan,
                perihal_kerosakan,
                nama_jawatan_pengadu,
                tarikh_pengadu,
                jumlah_kos_penyelenggaraan_terdahulu,
                anggaran_kos_penyelenggaraan,
                nama_jawatan_pegawai_aset,
                tarikh_pegawai_aset,
                syor_ulasan
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $complaint_id,
            $complaint['jenis_aset'] ?? '',
            $complaint['no_pendaftaran_aset'] ?? '',
            $complaint['nama_pengadu'] ?? '',
            $complaint['tarikh_kerosakan'] ?? null,
            $complaint['perihal_kerosakan'] ?? '',
            $complaint['nama_pengadu'] . ' - ' . ($complaint['jawatan'] ?? ''),
            $complaint['created_at'],
            $jumlah_kos_terdahulu,
            $anggaran_kos,
            $nama_pegawai,
            $tarikh_pegawai,
            $syor_ulasan
        ]);
        $borang_id = $db->lastInsertId();
    }

    if ($action === 'save_draft') {
        // Just save as draft - update workflow status to dalam_semakan_unit_aset
        if ($complaint['workflow_status'] === 'dimajukan_unit_aset') {
            $stmt = $db->prepare("
                UPDATE complaints SET
                    workflow_status = 'dalam_semakan_unit_aset',
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$complaint_id]);

            // Log workflow action
            $stmt = $db->prepare("
                INSERT INTO workflow_actions (
                    complaint_id, action_type, from_status, to_status, performed_by, remarks
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $complaint_id,
                'dalam_semakan_unit_aset',
                $complaint['workflow_status'],
                'dalam_semakan_unit_aset',
                $user['id'],
                'Unit Aset sedang menyemak dan mengisi Borang Kerosakan Aset Alih'
            ]);
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Draf berjaya disimpan']);

    } elseif ($action === 'forward_to_pelulus') {
        // Validate approval officer
        $approval_officer_id = intval($_POST['approval_officer_id'] ?? 0);

        if ($approval_officer_id <= 0) {
            throw new Exception('Sila pilih Pegawai Pelulus');
        }

        // Verify approval officer exists
        $stmt = $db->prepare("SELECT id FROM users WHERE id = ? AND role = 'bahagian_pentadbiran_kewangan'");
        $stmt->execute([$approval_officer_id]);
        if (!$stmt->fetch()) {
            throw new Exception('Pegawai Pelulus tidak sah');
        }

        // Update complaint workflow status
        $stmt = $db->prepare("
            UPDATE complaints SET
                workflow_status = 'dimajukan_pegawai_pelulus',
                unit_aset_processed_by = ?,
                unit_aset_processed_at = CURRENT_TIMESTAMP,
                approval_officer_id = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$user['id'], $approval_officer_id, $complaint_id]);

        // Log workflow action
        $stmt = $db->prepare("
            INSERT INTO workflow_actions (
                complaint_id, action_type, from_status, to_status, performed_by, remarks
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $complaint_id,
            'dimajukan_pegawai_pelulus',
            $complaint['workflow_status'],
            'dimajukan_pegawai_pelulus',
            $user['id'],
            'Borang Kerosakan Aset Alih telah lengkap dan dimajukan kepada Pegawai Pelulus untuk keputusan'
        ]);

        $db->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Borang berjaya dihantar kepada Pegawai Pelulus',
            'borang_id' => $borang_id
        ]);

    } else {
        throw new Exception('Tindakan tidak sah');
    }

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
