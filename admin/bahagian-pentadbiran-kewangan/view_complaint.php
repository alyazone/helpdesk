<?php
/**
 * Bahagian Pentadbiran & Kewangan - View and Approve Complaint
 * Fill Bahagian III of Borang Kerosakan Aset Alih
 */

require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !isBahagianPentadbiranKewangan()) {
    redirect('../../login.html');
}

$db = getDB();
$complaint_id = intval($_GET['id'] ?? 0);

if ($complaint_id <= 0) {
    redirect('complaints.php');
}

// Get complaint details with all related information
$stmt = $db->prepare("
    SELECT c.*,
           u_verified.nama_penuh as unit_aduan_officer,
           u_unit_aset.nama_penuh as unit_aset_officer,
           uao.nama as dimajukan_oleh_nama,
           dua.tindakan_susulan as tindakan_unit_aduan,
           dua.no_rujukan_fail
    FROM complaints c
    LEFT JOIN users u_verified ON c.unit_aduan_verified_by = u_verified.id
    LEFT JOIN users u_unit_aset ON c.unit_aset_processed_by = u_unit_aset.id
    LEFT JOIN unit_aset_officers uao ON c.dimajukan_ke = uao.id
    LEFT JOIN dokumen_unit_aduan dua ON c.id = dua.complaint_id
    WHERE c.id = ?
");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    redirect('complaints.php');
}

// Get borang kerosakan aset
$stmt = $db->prepare("SELECT * FROM borang_kerosakan_aset WHERE complaint_id = ?");
$stmt->execute([$complaint_id]);
$borang = $stmt->fetch();

if (!$borang) {
    // Redirect if borang doesn't exist
    redirect('complaints.php');
}

// Get attachments
$stmt = $db->prepare("SELECT * FROM attachments WHERE complaint_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$complaint_id]);
$attachments = $stmt->fetchAll();

$user = getUser();
$can_edit = in_array($complaint['workflow_status'], ['dimajukan_pegawai_pelulus']);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semakan & Kelulusan #<?php echo htmlspecialchars($complaint['ticket_number']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .print-section { page-break-inside: avoid; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="gradient-bg text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <a href="complaints.php" class="flex items-center space-x-2 hover:opacity-80">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali</span>
                    </a>
                    <span class="font-semibold text-lg">Semakan & Kelulusan</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm"><?php echo htmlspecialchars($user['nama']); ?></span>
                    <a href="../../api/logout.php" class="px-4 py-2 bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30 transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Log Keluar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Complaint Header -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800 mb-2">
                                <?php echo htmlspecialchars($complaint['perkara']); ?>
                            </h1>
                            <p class="text-gray-600">Tiket #<?php echo htmlspecialchars($complaint['ticket_number']); ?></p>
                        </div>
                        <div>
                            <?php
                            $workflow_colors = [
                                'dimajukan_pegawai_pelulus' => 'bg-yellow-100 text-yellow-800',
                                'diluluskan' => 'bg-green-100 text-green-800',
                                'ditolak' => 'bg-red-100 text-red-800',
                                'selesai' => 'bg-gray-100 text-gray-800'
                            ];
                            $workflow_labels = [
                                'dimajukan_pegawai_pelulus' => 'Perlu Kelulusan',
                                'diluluskan' => 'Diluluskan',
                                'ditolak' => 'Ditolak',
                                'selesai' => 'Selesai'
                            ];
                            $color = $workflow_colors[$complaint['workflow_status']] ?? 'bg-gray-100 text-gray-800';
                            $label = $workflow_labels[$complaint['workflow_status']] ?? $complaint['workflow_status'];
                            ?>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $color; ?>">
                                <?php echo $label; ?>
                            </span>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="font-semibold text-gray-800 mb-2">Keterangan Aduan:</h3>
                        <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($complaint['keterangan']); ?></p>
                    </div>
                </div>

                <!-- Bahagian I - Complaint Details -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <span class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                            <span class="font-bold text-orange-600">I</span>
                        </span>
                        Bahagian I - Maklumat Pengadu & Aset
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="text-sm text-gray-600">Nama Pengadu</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['nama_pengadu']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Email</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['email']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Jawatan</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['jawatan'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Bahagian</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['bahagian'] ?? '-'); ?></p>
                        </div>
                    </div>

                    <?php if (!empty($borang['jenis_aset'])): ?>
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <h3 class="font-semibold text-gray-700 mb-3">Maklumat Aset:</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600">Jenis Aset</label>
                                <p class="font-medium"><?php echo htmlspecialchars($borang['jenis_aset']); ?></p>
                            </div>
                            <?php if (!empty($borang['no_siri_pendaftaran_aset'])): ?>
                            <div>
                                <label class="text-sm text-gray-600">No. Siri/Pendaftaran</label>
                                <p class="font-medium"><?php echo htmlspecialchars($borang['no_siri_pendaftaran_aset']); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($borang['tarikh_kerosakan'])): ?>
                            <div>
                                <label class="text-sm text-gray-600">Tarikh Kerosakan</label>
                                <p class="font-medium"><?php echo date('d/m/Y', strtotime($borang['tarikh_kerosakan'])); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($borang['perihal_kerosakan'])): ?>
                            <div class="md:col-span-2">
                                <label class="text-sm text-gray-600">Perihal Kerosakan</label>
                                <p class="font-medium"><?php echo htmlspecialchars($borang['perihal_kerosakan']); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Bahagian II - Unit Aset Review -->
                <div class="bg-blue-50 rounded-xl border border-blue-200 p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <span class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <span class="font-bold text-blue-600">II</span>
                        </span>
                        Bahagian II - Semakan Pegawai Aset
                    </h2>

                    <div class="bg-white rounded-lg p-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600">Jumlah Kos Penyelenggaraan Terdahulu (RM)</label>
                                <p class="font-semibold text-lg"><?php echo number_format($borang['jumlah_kos_penyelenggaraan_terdahulu'], 2); ?></p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Anggaran Kos Penyelenggaraan/Pembaikan (RM)</label>
                                <p class="font-semibold text-lg text-orange-600"><?php echo number_format($borang['anggaran_kos_penyelenggaraan'], 2); ?></p>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm text-gray-600">Syor/Ulasan Pegawai Aset</label>
                            <div class="bg-gray-50 p-3 rounded-lg mt-1">
                                <p class="text-gray-800 whitespace-pre-wrap"><?php echo htmlspecialchars($borang['syor_ulasan']); ?></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600">Nama Pegawai Aset</label>
                                <p class="font-medium"><?php echo htmlspecialchars($borang['nama_jawatan_pegawai_aset']); ?></p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Tarikh</label>
                                <p class="font-medium"><?php echo date('d/m/Y', strtotime($borang['tarikh_pegawai_aset'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bahagian III - Keputusan (Approval Section) -->
                <?php if ($can_edit): ?>
                <div class="bg-green-50 rounded-xl border-2 border-green-300 p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <span class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <span class="font-bold text-green-600">III</span>
                        </span>
                        Bahagian III - Keputusan Ketua Jabatan/Bahagian
                    </h2>
                    <p class="text-sm text-gray-600 mb-6">Sila isi keputusan kelulusan untuk aduan ini</p>

                    <form id="approvalForm" class="space-y-6">
                        <input type="hidden" name="complaint_id" value="<?php echo $complaint_id; ?>">

                        <!-- Decision Status -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Keputusan <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center p-4 bg-white border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition">
                                    <input type="radio" name="keputusan_status" value="diluluskan" required
                                           class="w-5 h-5 text-green-600 focus:ring-green-500">
                                    <span class="ml-3 flex items-center">
                                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                        <span class="font-semibold">Diluluskan</span>
                                    </span>
                                </label>
                                <label class="flex items-center p-4 bg-white border-2 border-gray-300 rounded-lg cursor-pointer hover:border-red-500 transition">
                                    <input type="radio" name="keputusan_status" value="ditolak" required
                                           class="w-5 h-5 text-red-600 focus:ring-red-500">
                                    <span class="ml-3 flex items-center">
                                        <i class="fas fa-times-circle text-red-600 mr-2"></i>
                                        <span class="font-semibold">Ditolak</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Comments/Remarks -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Ulasan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="keputusan_ulasan" rows="5" required
                                      placeholder="Masukkan ulasan atau catatan berkaitan keputusan..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"></textarea>
                            <p class="text-xs text-gray-500 mt-1">Ulasan akan dipaparkan dalam borang rasmi</p>
                        </div>

                        <!-- Officer Name and Position -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nama <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="keputusan_nama" required
                                       value="<?php echo htmlspecialchars($user['nama']); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Jawatan <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="keputusan_jawatan" required
                                       value="<?php echo htmlspecialchars($_SESSION['jawatan'] ?? 'Pegawai Kewangan'); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="w-full md:w-1/2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Tarikh <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="keputusan_tarikh" required
                                   value="<?php echo date('Y-m-d'); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                        </div>

                        <!-- Signature Note -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                                <div>
                                    <p class="text-sm text-blue-800 font-medium">Nota: Tandatangan Dijana oleh Komputer</p>
                                    <p class="text-xs text-blue-600 mt-1">
                                        Borang rasmi akan memaparkan catatan "Tandatangan ini dijana oleh komputer dan sah tanpa tandatangan fizikal"
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex gap-4 pt-4 border-t border-gray-200">
                            <button type="submit" name="action" value="submit_decision"
                                    class="flex-1 px-8 py-4 gradient-bg text-white rounded-lg hover:opacity-90 transition font-semibold text-lg">
                                <i class="fas fa-check mr-2"></i>Hantar Keputusan
                            </button>
                            <a href="complaints.php"
                               class="px-8 py-4 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition font-semibold">
                                <i class="fas fa-times mr-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
                <?php else: ?>
                <!-- Display Decision (Read-only) -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <span class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <span class="font-bold text-green-600">III</span>
                        </span>
                        Bahagian III - Keputusan
                    </h2>

                    <?php if (!empty($borang['keputusan_status']) && $borang['keputusan_status'] !== 'pending'): ?>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm text-gray-600">Keputusan</label>
                            <?php
                            $decision_color = $borang['keputusan_status'] === 'diluluskan' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                            $decision_icon = $borang['keputusan_status'] === 'diluluskan' ? 'fa-check-circle' : 'fa-times-circle';
                            $decision_label = $borang['keputusan_status'] === 'diluluskan' ? 'DILULUSKAN' : 'DITOLAK';
                            ?>
                            <div class="mt-2">
                                <span class="inline-flex items-center px-4 py-2 rounded-lg font-bold text-lg <?php echo $decision_color; ?>">
                                    <i class="fas <?php echo $decision_icon; ?> mr-2"></i>
                                    <?php echo $decision_label; ?>
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm text-gray-600">Ulasan</label>
                            <div class="bg-gray-50 p-4 rounded-lg mt-1">
                                <p class="text-gray-800 whitespace-pre-wrap"><?php echo htmlspecialchars($borang['keputusan_ulasan']); ?></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600">Nama</label>
                                <p class="font-medium"><?php echo htmlspecialchars($borang['keputusan_nama']); ?></p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Jawatan</label>
                                <p class="font-medium"><?php echo htmlspecialchars($borang['keputusan_jawatan']); ?></p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Tarikh</label>
                                <p class="font-medium"><?php echo date('d/m/Y', strtotime($borang['keputusan_tarikh'])); ?></p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-200">
                            <a href="generate_borang_final.php?id=<?php echo $complaint_id; ?>" target="_blank"
                               class="inline-flex items-center px-6 py-3 gradient-bg text-white rounded-lg hover:opacity-90 transition">
                                <i class="fas fa-file-pdf mr-2"></i>Muat Turun Borang Lengkap (PDF)
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <p class="text-gray-500 text-center py-8">
                        <i class="fas fa-hourglass-half text-3xl mb-2"></i><br>
                        Keputusan belum dibuat
                    </p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Attachments -->
                <?php if (!empty($attachments)): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Lampiran</h2>
                    <div class="space-y-2">
                        <?php foreach ($attachments as $attachment): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-file text-orange-600 text-xl"></i>
                                <div>
                                    <p class="font-medium"><?php echo htmlspecialchars($attachment['file_original_name']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo formatFileSize($attachment['file_size']); ?></p>
                                </div>
                            </div>
                            <a href="<?php echo htmlspecialchars($attachment['file_path']); ?>"
                               target="_blank"
                               class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition">
                                <i class="fas fa-download mr-2"></i>Muat Turun
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Status Card -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="font-bold text-gray-800 mb-4">Status Workflow</h3>
                    <?php
                    $color = $workflow_colors[$complaint['workflow_status']] ?? 'bg-gray-100 text-gray-800';
                    $label = $workflow_labels[$complaint['workflow_status']] ?? $complaint['workflow_status'];
                    ?>
                    <div class="text-center p-4 rounded-lg <?php echo $color; ?>">
                        <p class="text-xl font-bold"><?php echo $label; ?></p>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="font-bold text-gray-800 mb-4">Sejarah Workflow</h3>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mt-2 mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">Aduan Dibuat</p>
                                <p class="text-xs text-gray-500"><?php echo date('d/m/Y H:i', strtotime($complaint['created_at'])); ?></p>
                            </div>
                        </div>
                        <?php if (!empty($complaint['unit_aduan_verified_at'])): ?>
                        <div class="flex items-start">
                            <div class="w-2 h-2 bg-purple-500 rounded-full mt-2 mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">Disahkan Unit Aduan</p>
                                <p class="text-xs text-gray-500"><?php echo date('d/m/Y H:i', strtotime($complaint['unit_aduan_verified_at'])); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($complaint['unit_aset_processed_at'])): ?>
                        <div class="flex items-start">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">Diproses Unit Aset</p>
                                <p class="text-xs text-gray-500"><?php echo date('d/m/Y H:i', strtotime($complaint['unit_aset_processed_at'])); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($borang['keputusan_tarikh']) && $borang['keputusan_status'] !== 'pending'): ?>
                        <div class="flex items-start">
                            <div class="w-2 h-2 <?php echo $borang['keputusan_status'] === 'diluluskan' ? 'bg-green-500' : 'bg-red-500'; ?> rounded-full mt-2 mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">
                                    <?php echo $borang['keputusan_status'] === 'diluluskan' ? 'Diluluskan' : 'Ditolak'; ?>
                                </p>
                                <p class="text-xs text-gray-500"><?php echo date('d/m/Y', strtotime($borang['keputusan_tarikh'])); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <?php if (!$can_edit && $borang['keputusan_status'] !== 'pending'): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="font-bold text-gray-800 mb-4">Tindakan Pantas</h3>
                    <div class="space-y-2">
                        <a href="generate_borang_final.php?id=<?php echo $complaint_id; ?>" target="_blank"
                           class="block w-full px-4 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition text-center">
                            <i class="fas fa-file-pdf mr-2"></i>Borang PDF
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('approvalForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const keputusan = formData.get('keputusan_status');

            // Confirm before submitting
            const confirmMsg = keputusan === 'diluluskan'
                ? 'Adakah anda pasti untuk MELULUSKAN permohonan ini? Tindakan ini tidak boleh dibatalkan.'
                : 'Adakah anda pasti untuk MENOLAK permohonan ini? Tindakan ini tidak boleh dibatalkan.';

            if (!confirm(confirmMsg)) {
                return;
            }

            try {
                const response = await fetch('process_approval.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert('Keputusan berjaya dihantar!');
                    window.location.reload();
                } else {
                    alert(result.message || 'Gagal memproses keputusan');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ralat sambungan. Sila cuba lagi.');
            }
        });
    </script>
</body>
</html>
