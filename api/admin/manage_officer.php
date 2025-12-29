<?php
/**
 * API - Manage Officers (Admin Only)
 */

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    jsonResponse(false, 'Akses ditolak');
}

$db = getDB();

// Determine action
$action = $_POST['action'] ?? 'save';

try {
    if ($action === 'delete') {
        // Delete officer
        $officer_id = intval($_POST['officer_id'] ?? 0);

        if ($officer_id <= 0) {
            jsonResponse(false, 'ID pegawai tidak sah');
        }

        // Check if officer exists
        $stmt = $db->prepare("SELECT * FROM officers WHERE id = ?");
        $stmt->execute([$officer_id]);
        $officer = $stmt->fetch();

        if (!$officer) {
            jsonResponse(false, 'Pegawai tidak dijumpai');
        }

        // Check if officer is assigned to any complaints
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM complaints WHERE officer_id = ?");
        $stmt->execute([$officer_id]);
        $complaint_count = $stmt->fetch()['count'];

        if ($complaint_count > 0) {
            jsonResponse(false, "Pegawai tidak boleh dipadam kerana telah ditugaskan kepada {$complaint_count} aduan. Sila ubah status kepada 'Tidak Bertugas' sebaliknya.");
        }

        // Delete officer
        $stmt = $db->prepare("DELETE FROM officers WHERE id = ?");
        $stmt->execute([$officer_id]);

        jsonResponse(true, 'Pegawai berjaya dipadam');

    } else {
        // Add or update officer
        $officer_id = intval($_POST['officer_id'] ?? 0);
        $nama = sanitize($_POST['nama']);
        $email = sanitize($_POST['email'] ?? '');
        $no_telefon = sanitize($_POST['no_telefon'] ?? '');
        $status = sanitize($_POST['status']);

        // Validate required fields
        if (empty($nama) || empty($status)) {
            jsonResponse(false, 'Sila lengkapkan semua medan yang diperlukan');
        }

        // Validate email format if provided
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(false, 'Format email tidak sah');
        }

        // Validate status
        if (!in_array($status, ['bertugas', 'tidak_bertugas'])) {
            jsonResponse(false, 'Status tidak sah');
        }

        if ($officer_id > 0) {
            // Update existing officer
            $stmt = $db->prepare("SELECT * FROM officers WHERE id = ?");
            $stmt->execute([$officer_id]);
            $existing_officer = $stmt->fetch();

            if (!$existing_officer) {
                jsonResponse(false, 'Pegawai tidak dijumpai');
            }

            // Check if email is already used by another officer
            if (!empty($email)) {
                $stmt = $db->prepare("SELECT id FROM officers WHERE email = ? AND id != ?");
                $stmt->execute([$email, $officer_id]);
                if ($stmt->fetch()) {
                    jsonResponse(false, 'Email telah digunakan oleh pegawai lain');
                }
            }

            // Update officer
            $stmt = $db->prepare("
                UPDATE officers
                SET nama = ?, email = ?, no_telefon = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $nama,
                $email,
                $no_telefon,
                $status,
                $officer_id
            ]);

            jsonResponse(true, 'Pegawai berjaya dikemaskini');

        } else {
            // Create new officer
            // Check if email already exists
            if (!empty($email)) {
                $stmt = $db->prepare("SELECT id FROM officers WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    jsonResponse(false, 'Email telah digunakan');
                }
            }

            // Insert new officer
            $stmt = $db->prepare("
                INSERT INTO officers (nama, email, no_telefon, status)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $nama,
                $email,
                $no_telefon,
                $status
            ]);

            jsonResponse(true, 'Pegawai baru berjaya ditambah');
        }
    }
} catch (Exception $e) {
    jsonResponse(false, 'Ralat: ' . $e->getMessage());
}
