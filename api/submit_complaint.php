<?php
/**
 * Submit Complaint API
 * Handles complaint/suggestion submission
 */

require_once __DIR__ . '/../config/config.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

// Get POST data (FormData or JSON)
$isJson = false;
if (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $data = json_decode(file_get_contents('php://input'), true);
    $isJson = true;
} else {
    $data = $_POST;
}

// Extract and sanitize data
$jenis = sanitize($data['jenis'] ?? '');
$perkara = sanitize($data['perkara'] ?? '');
$keterangan = sanitize($data['keterangan'] ?? '');

// User details
$nama_pengadu = sanitize($data['nama'] ?? '');
$alamat = sanitize($data['alamat'] ?? '');
$no_telefon = sanitize($data['telefon'] ?? '');
$poskod = sanitize($data['poskod'] ?? '');
$jawatan = sanitize($data['jawatan'] ?? '');
$bahagian = sanitize($data['bahagian'] ?? '');
$tingkat = sanitize($data['tingkat'] ?? '');
$email = sanitize($data['emel'] ?? '');
$no_sambungan = sanitize($data['sambungan'] ?? '');

// Asset details
$jenis_aset = sanitize($data['jenisAset'] ?? '');
$no_pendaftaran_aset = sanitize($data['noPendaftaran'] ?? '');
$pengguna_akhir = sanitize($data['penggunaAkhir'] ?? '');
$tarikh_kerosakan = sanitize($data['tarikhKerosakan'] ?? '');
$perihal_kerosakan = sanitize($data['perihalKerosakan'] ?? '');
$perihal_kerosakan_value = sanitize($data['perihalKerosakanValue'] ?? '');

// Officer
$pegawai_penerima = sanitize($data['pegawai'] ?? 'En. Ahmad Bin Abdullah');

// Validation
$errors = [];

if (empty($jenis)) $errors[] = 'Jenis aduan diperlukan';
if (empty($perkara)) $errors[] = 'Perkara diperlukan';
if (empty($keterangan)) $errors[] = 'Keterangan diperlukan';
if (empty($nama_pengadu)) $errors[] = 'Nama penuh diperlukan';
if (empty($email)) $errors[] = 'Alamat emel diperlukan';
if (empty($jawatan)) $errors[] = 'Jawatan diperlukan';

// Validate email domain
if (!empty($email) && !validateEmailDomain($email)) {
    $errors[] = 'Hanya alamat emel jabatan (@jpbdselangor.gov.my) yang dibenarkan';
}

if (!empty($errors)) {
    jsonResponse(false, implode(', ', $errors));
}

try {
    $db = getDB();

    // Generate unique ticket number
    do {
        $ticket_number = generateTicketNumber();
        $stmt = $db->prepare("SELECT id FROM complaints WHERE ticket_number = ?");
        $stmt->execute([$ticket_number]);
    } while ($stmt->fetch());

    // Get officer ID
    $stmt = $db->prepare("SELECT id FROM officers WHERE nama = ? LIMIT 1");
    $stmt->execute([$pegawai_penerima]);
    $officer = $stmt->fetch();
    $officer_id = $officer['id'] ?? null;

    // Get user ID if logged in
    $user_id = $_SESSION['user_id'] ?? null;

    // Insert complaint
    $stmt = $db->prepare("
        INSERT INTO complaints (
            ticket_number, jenis, perkara, keterangan,
            user_id, nama_pengadu, alamat, no_telefon, poskod, jawatan, bahagian, tingkat, email, no_sambungan,
            jenis_aset, no_pendaftaran_aset, pengguna_akhir, tarikh_kerosakan, perihal_kerosakan, perihal_kerosakan_value,
            officer_id, pegawai_penerima, status, progress
        ) VALUES (
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, 'pending', 0
        )
    ");

    $stmt->execute([
        $ticket_number, $jenis, $perkara, $keterangan,
        $user_id, $nama_pengadu, $alamat, $no_telefon, $poskod, $jawatan, $bahagian, $tingkat, $email, $no_sambungan,
        $jenis_aset, $no_pendaftaran_aset, $pengguna_akhir, $tarikh_kerosakan, $perihal_kerosakan, $perihal_kerosakan_value,
        $officer_id, $pegawai_penerima
    ]);

    $complaint_id = $db->lastInsertId();

    // Add initial status history
    $stmt = $db->prepare("
        INSERT INTO complaint_status_history (complaint_id, status, keterangan, created_by)
        VALUES (?, 'Aduan Diterima', 'Aduan/Cadangan telah diterima dan akan diproses', ?)
    ");
    $stmt->execute([$complaint_id, $user_id]);

    // Handle file uploads (if not JSON request)
    $uploaded_files = [];
    if (!$isJson && isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
        $files = $_FILES['files'];
        $file_count = count($files['name']);

        for ($i = 0; $i < $file_count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $file_tmp = $files['tmp_name'][$i];
                $file_name = $files['name'][$i];
                $file_size = $files['size'][$i];
                $file_type = $files['type'][$i];

                // Validate file size
                if ($file_size > MAX_FILE_SIZE) {
                    continue;
                }

                // Validate file type
                if (!in_array($file_type, ALLOWED_FILE_TYPES)) {
                    continue;
                }

                // Generate unique file name
                $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $new_file_name = uniqid() . '_' . time() . '.' . $file_ext;
                $upload_path = UPLOAD_DIR . $new_file_name;

                // Move uploaded file
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Save to database
                    $stmt = $db->prepare("
                        INSERT INTO attachments (complaint_id, file_name, file_original_name, file_path, file_size, file_type)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $complaint_id,
                        $new_file_name,
                        $file_name,
                        $upload_path,
                        $file_size,
                        $file_type
                    ]);

                    $uploaded_files[] = $file_name;
                }
            }
        }
    }

    jsonResponse(true, 'Aduan/Cadangan telah berjaya dihantar', [
        'ticket_number' => $ticket_number,
        'complaint_id' => $complaint_id,
        'uploaded_files' => $uploaded_files
    ]);

} catch (PDOException $e) {
    jsonResponse(false, 'Ralat sistem: ' . $e->getMessage());
}
