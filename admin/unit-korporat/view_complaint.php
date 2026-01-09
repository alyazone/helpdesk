<?php
/**
 * Unit Korporat - View Complaint (Read-Only)
 * View complete complaint details including all workflow history
 */

require_once __DIR__ . '/../../config/config.php';

// Check if user is logged in and has Unit Korporat role
if (!isLoggedIn() || !isUnitKorporat()) {
    redirect('../../login.html');
}

$complaintId = $_GET['id'] ?? null;

if (!$complaintId) {
    redirect('complaints.php');
}

$db = getDB();
$user = getUser();

try {
    // Get complaint details with all related information
    $stmt = $db->prepare("
        SELECT c.*,
               u.nama_penuh as user_name,
               u.email as user_email,
               u.no_sambungan as user_phone,
               u.bahagian as user_department,
               u.unit as user_unit,
               uad.nama as unit_aduan_officer,
               ua.nama as unit_aset_officer,
               pp.nama as pegawai_pelulus_name,
               uit.nama as unit_it_officer_name
        FROM complaints c
        LEFT JOIN users u ON c.user_id = u.id
        LEFT JOIN users uad ON c.unit_aduan_verified_by = uad.id
        LEFT JOIN unit_aset_officers ua ON c.unit_aset_officer_id = ua.id
        LEFT JOIN users pp ON c.pegawai_pelulus_id = pp.id
        LEFT JOIN unit_it_sokongan_officers uit ON c.unit_it_officer_id = uit.id
        WHERE c.id = ?
    ");
    $stmt->execute([$complaintId]);
    $complaint = $stmt->fetch();

    if (!$complaint) {
        redirect('complaints.php');
    }

} catch (Exception $e) {
    error_log("Error fetching complaint: " . $e->getMessage());
    redirect('complaints.php');
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Aduan - Unit Korporat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-bar-chart-line"></i> Unit Korporat (Laporan)
            </a>
            <div class="ms-auto">
                <a href="complaints.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>Mod Lihat Sahaja:</strong> Anda melihat aduan ini dalam mod baca sahaja. Sebarang tindakan perlu dilakukan oleh unit yang berkaitan.
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-file-text"></i> Maklumat Aduan</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nombor Tiket:</label>
                        <p class="form-control-plaintext"><?php echo htmlspecialchars($complaint['ticket_number']); ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Status:</label>
                        <p class="form-control-plaintext">
                            <?php
                            $statusMap = [
                                'baru' => ['badge' => 'primary', 'text' => 'Baru'],
                                'disahkan_unit_aduan' => ['badge' => 'info', 'text' => 'Disahkan Unit Aduan'],
                                'dimajukan_unit_aset' => ['badge' => 'warning', 'text' => 'Dimajukan Unit Aset'],
                                'dalam_semakan_unit_aset' => ['badge' => 'warning', 'text' => 'Dalam Semakan Unit Aset'],
                                'dimajukan_pegawai_pelulus' => ['badge' => 'warning', 'text' => 'Menunggu Kelulusan'],
                                'diluluskan' => ['badge' => 'info', 'text' => 'Diluluskan'],
                                'ditolak' => ['badge' => 'danger', 'text' => 'Ditolak'],
                                'dimajukan_unit_it' => ['badge' => 'info', 'text' => 'Dimajukan Unit IT'],
                                'selesai' => ['badge' => 'success', 'text' => 'Selesai']
                            ];
                            $status = $statusMap[$complaint['workflow_status']] ?? ['badge' => 'secondary', 'text' => $complaint['workflow_status']];
                            ?>
                            <span class="badge bg-<?php echo $status['badge']; ?> fs-6">
                                <?php echo $status['text']; ?>
                            </span>
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Tajuk Aduan:</label>
                        <p class="form-control-plaintext"><?php echo htmlspecialchars($complaint['tajuk']); ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Keterangan:</label>
                        <div class="border rounded p-3 bg-light">
                            <?php echo nl2br(htmlspecialchars($complaint['keterangan'])); ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Kategori:</label>
                        <p class="form-control-plaintext"><?php echo htmlspecialchars($complaint['kategori_aduan']); ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Keutamaan:</label>
                        <p class="form-control-plaintext">
                            <?php
                            $priorityMap = ['Rendah' => 'success', 'Sederhana' => 'warning', 'Tinggi' => 'danger', 'Kritikal' => 'dark'];
                            $priorityBadge = $priorityMap[$complaint['keutamaan']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?php echo $priorityBadge; ?>">
                                <?php echo htmlspecialchars($complaint['keutamaan']); ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Tarikh Dikemukakan:</label>
                        <p class="form-control-plaintext"><?php echo date('d/m/Y H:i', strtotime($complaint['created_at'])); ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Lokasi:</label>
                        <p class="form-control-plaintext"><?php echo htmlspecialchars($complaint['lokasi']); ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">No. Inventori/Aset (jika ada):</label>
                        <p class="form-control-plaintext"><?php echo htmlspecialchars($complaint['no_inventori'] ?: '-'); ?></p>
                    </div>
                </div>

                <?php if ($complaint['gambar_lampiran']): ?>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Lampiran Gambar:</label>
                        <div>
                            <img src="../../uploads/<?php echo htmlspecialchars($complaint['gambar_lampiran']); ?>"
                                 alt="Lampiran"
                                 class="img-fluid rounded border"
                                 style="max-height: 400px;">
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- User Information -->
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-person"></i> Maklumat Pengadu</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nama:</strong> <?php echo htmlspecialchars($complaint['user_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($complaint['user_email']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>No. Sambungan:</strong> <?php echo htmlspecialchars($complaint['user_phone'] ?: '-'); ?></p>
                        <p><strong>Bahagian:</strong> <?php echo htmlspecialchars($complaint['user_department'] ?: '-'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workflow History -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Sejarah Proses</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php if ($complaint['unit_aduan_verified_by']): ?>
                    <div class="mb-3">
                        <strong><i class="bi bi-check-circle text-success"></i> Disahkan oleh Unit Aduan Dalaman</strong>
                        <p class="mb-1">Pegawai: <?php echo htmlspecialchars($complaint['unit_aduan_officer']); ?></p>
                        <p class="text-muted small">Tarikh: <?php echo $complaint['unit_aduan_verified_at'] ? date('d/m/Y H:i', strtotime($complaint['unit_aduan_verified_at'])) : '-'; ?></p>
                        <?php if ($complaint['unit_aduan_remarks']): ?>
                        <p class="text-muted"><em>Catatan: <?php echo htmlspecialchars($complaint['unit_aduan_remarks']); ?></em></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($complaint['unit_aset_officer_id']): ?>
                    <div class="mb-3">
                        <strong><i class="bi bi-box text-warning"></i> Dimajukan ke Unit Aset</strong>
                        <p class="mb-1">Pegawai: <?php echo htmlspecialchars($complaint['unit_aset_officer']); ?></p>
                        <?php if ($complaint['unit_aset_remarks']): ?>
                        <p class="text-muted"><em>Catatan: <?php echo htmlspecialchars($complaint['unit_aset_remarks']); ?></em></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($complaint['pegawai_pelulus_id']): ?>
                    <div class="mb-3">
                        <strong><i class="bi bi-clipboard-check text-info"></i> Keputusan Pegawai Pelulus</strong>
                        <p class="mb-1">Pegawai: <?php echo htmlspecialchars($complaint['pegawai_pelulus_name']); ?></p>
                        <p class="mb-1">Keputusan: <span class="badge bg-<?php echo $complaint['workflow_status'] === 'diluluskan' ? 'success' : 'danger'; ?>">
                            <?php echo $complaint['workflow_status'] === 'diluluskan' ? 'Diluluskan' : 'Ditolak'; ?>
                        </span></p>
                        <p class="text-muted small">Tarikh: <?php echo $complaint['pegawai_pelulus_processed_at'] ? date('d/m/Y H:i', strtotime($complaint['pegawai_pelulus_processed_at'])) : '-'; ?></p>
                        <?php if ($complaint['pegawai_pelulus_remarks']): ?>
                        <p class="text-muted"><em>Catatan: <?php echo htmlspecialchars($complaint['pegawai_pelulus_remarks']); ?></em></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($complaint['unit_it_officer_id']): ?>
                    <div class="mb-3">
                        <strong><i class="bi bi-tools text-primary"></i> Unit IT Sokongan</strong>
                        <p class="mb-1">Pegawai: <?php echo htmlspecialchars($complaint['unit_it_officer_name']); ?></p>
                        <?php if ($complaint['workflow_status'] === 'selesai'): ?>
                        <p class="mb-1"><span class="badge bg-success">Selesai</span></p>
                        <p class="text-muted small">Tarikh Selesai: <?php echo $complaint['unit_it_completed_at'] ? date('d/m/Y H:i', strtotime($complaint['unit_it_completed_at'])) : '-'; ?></p>
                        <?php if ($complaint['unit_it_completion_remarks']): ?>
                        <p class="text-muted"><em>Catatan: <?php echo htmlspecialchars($complaint['unit_it_completion_remarks']); ?></em></p>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="text-center mb-4">
            <a href="complaints.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Senarai
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/role-switcher.js"></script>
</body>
</html>
