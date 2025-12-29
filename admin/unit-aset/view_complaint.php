<?php
/**
 * Unit Aset - View Complaint and Fill Borang Kerosakan Aset Alih
 */

require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !isUnitAset()) {
    redirect('../../login.html');
}

$db = getDB();
$complaint_id = intval($_GET['id'] ?? 0);

if ($complaint_id <= 0) {
    redirect('complaints.php');
}

// Get complaint details
$stmt = $db->prepare("
    SELECT c.*,
           u_verified.nama_penuh as unit_aduan_officer,
           uao.nama as dimajukan_oleh_nama,
           dua.tindakan_susulan as tindakan_unit_aduan,
           dua.no_rujukan_fail
    FROM complaints c
    LEFT JOIN users u_verified ON c.unit_aduan_verified_by = u_verified.id
    LEFT JOIN unit_aset_officers uao ON c.dimajukan_ke = uao.id
    LEFT JOIN dokumen_unit_aduan dua ON c.id = dua.complaint_id
    WHERE c.id = ?
");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    redirect('complaints.php');
}

// Get borang kerosakan aset if exists
$stmt = $db->prepare("SELECT * FROM borang_kerosakan_aset WHERE complaint_id = ?");
$stmt->execute([$complaint_id]);
$borang = $stmt->fetch();

// Get attachments
$stmt = $db->prepare("SELECT * FROM attachments WHERE complaint_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$complaint_id]);
$attachments = $stmt->fetchAll();

// Get approval officers (users with role bahagian_pentadbiran_kewangan)
$stmt = $db->query("SELECT id, nama_penuh, email, jawatan FROM users WHERE role = 'bahagian_pentadbiran_kewangan' ORDER BY nama_penuh");
$approval_officers = $stmt->fetchAll();

$user = getUser();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Aduan #<?php echo htmlspecialchars($complaint['ticket_number']); ?> - Unit Aset</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
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
                    <span class="font-semibold text-lg">Proses Aduan</span>
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
                        <div class="flex gap-2">
                            <?php
                            $workflow_colors = [
                                'dimajukan_unit_aset' => 'bg-yellow-100 text-yellow-800',
                                'dalam_semakan_unit_aset' => 'bg-purple-100 text-purple-800',
                                'dimajukan_pegawai_pelulus' => 'bg-indigo-100 text-indigo-800',
                                'diluluskan' => 'bg-green-100 text-green-800',
                                'selesai' => 'bg-green-100 text-green-800'
                            ];
                            $workflow_labels = [
                                'dimajukan_unit_aset' => 'Baru Diterima',
                                'dalam_semakan_unit_aset' => 'Dalam Semakan',
                                'dimajukan_pegawai_pelulus' => 'Dimajukan ke Pelulus',
                                'diluluskan' => 'Diluluskan',
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
                        <h3 class="font-semibold text-gray-800 mb-2">Keterangan:</h3>
                        <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($complaint['keterangan']); ?></p>
                    </div>

                    <?php if (!empty($complaint['tindakan_unit_aduan'])): ?>
                    <div class="border-t border-gray-200 pt-4 mt-4 bg-purple-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-purple-800 mb-2">
                            <i class="fas fa-info-circle mr-2"></i>Tindakan dari Unit Aduan Dalaman:
                        </h3>
                        <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($complaint['tindakan_unit_aduan']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Complainant Details -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Maklumat Pengadu</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-600">Nama</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['nama_pengadu']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Email</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['email']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">No. Telefon</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['no_telefon'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Jawatan</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['jawatan'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Bahagian</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['bahagian'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Tingkat</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['tingkat'] ?? '-'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Asset Details -->
                <?php if (!empty($complaint['jenis_aset'])): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Maklumat Aset (Bahagian I - Diisi oleh Pengadu)</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-600">Jenis Aset</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['jenis_aset']); ?></p>
                        </div>
                        <?php if (!empty($complaint['no_pendaftaran_aset'])): ?>
                        <div>
                            <label class="text-sm text-gray-600">No. Siri/Pendaftaran Aset</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['no_pendaftaran_aset']); ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($complaint['tarikh_kerosakan'])): ?>
                        <div>
                            <label class="text-sm text-gray-600">Tarikh Kerosakan</label>
                            <p class="font-medium"><?php echo date('d/m/Y', strtotime($complaint['tarikh_kerosakan'])); ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($complaint['perihal_kerosakan'])): ?>
                        <div class="md:col-span-2">
                            <label class="text-sm text-gray-600">Perihal Kerosakan</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['perihal_kerosakan']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
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
                                <i class="fas fa-file text-blue-600 text-xl"></i>
                                <div>
                                    <p class="font-medium"><?php echo htmlspecialchars($attachment['file_original_name']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo formatFileSize($attachment['file_size']); ?></p>
                                </div>
                            </div>
                            <a href="<?php echo htmlspecialchars($attachment['file_path']); ?>"
                               target="_blank"
                               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-download mr-2"></i>Muat Turun
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Borang Kerosakan Aset Alih - Section II (Unit Aset) -->
                <?php if ($complaint['workflow_status'] === 'dimajukan_unit_aset' || $complaint['workflow_status'] === 'dalam_semakan_unit_aset'): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-edit mr-2 text-blue-600"></i>
                        Borang Kerosakan Aset Alih - Bahagian II
                    </h2>
                    <p class="text-sm text-gray-600 mb-6">Untuk diisi oleh Pegawai Aset/Pegawai Teknikal</p>

                    <form id="borangAsetForm" class="space-y-6">
                        <input type="hidden" name="complaint_id" value="<?php echo $complaint_id; ?>">

                        <!-- Jumlah Kos Penyelenggaraan Terdahulu -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Jumlah Kos Penyelenggaraan Terdahulu (RM)
                            </label>
                            <input type="number" step="0.01" name="jumlah_kos_penyelenggaraan_terdahulu"
                                   value="<?php echo $borang['jumlah_kos_penyelenggaraan_terdahulu'] ?? '0.00'; ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Anggaran Kos Penyelenggaraan -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Anggaran Kos Penyelenggaraan/Pembaikan (RM) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.01" name="anggaran_kos_penyelenggaraan" required
                                   value="<?php echo $borang['anggaran_kos_penyelenggaraan'] ?? ''; ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Syor/Ulasan -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Syor/Ulasan Pegawai Aset <span class="text-red-500">*</span>
                            </label>
                            <textarea name="syor_ulasan" rows="4" required
                                      placeholder="Contoh: Disyorkan untuk dibaiki kerana masih dalam keadaan baik dan boleh digunakan..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($borang['syor_ulasan'] ?? ''); ?></textarea>
                        </div>

                        <!-- Nama & Jawatan Pegawai Aset -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nama Pegawai Aset <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nama_jawatan_pegawai_aset" required
                                       value="<?php echo $borang['nama_jawatan_pegawai_aset'] ?? $user['nama']; ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Tarikh <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="tarikh_pegawai_aset" required
                                       value="<?php echo $borang['tarikh_pegawai_aset'] ?? date('Y-m-d'); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- Approval Officer Selection -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Dimajukan ke Pegawai Pelulus <span class="text-red-500">*</span>
                            </label>
                            <select name="approval_officer_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Pegawai Pelulus --</option>
                                <?php foreach ($approval_officers as $officer): ?>
                                <option value="<?php echo $officer['id']; ?>">
                                    <?php echo htmlspecialchars($officer['nama_penuh']); ?>
                                    <?php if (!empty($officer['jawatan'])): ?>
                                        - <?php echo htmlspecialchars($officer['jawatan']); ?>
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-4 pt-4">
                            <button type="submit" name="action" value="save_draft"
                                    class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                                <i class="fas fa-save mr-2"></i>Simpan Draf
                            </button>
                            <button type="submit" name="action" value="forward_to_pelulus"
                                    class="px-6 py-3 gradient-bg text-white rounded-lg hover:opacity-90 transition">
                                <i class="fas fa-paper-plane mr-2"></i>Hantar ke Pegawai Pelulus
                            </button>
                            <?php if ($borang): ?>
                            <a href="generate_borang.php?id=<?php echo $complaint_id; ?>" target="_blank"
                               class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                <i class="fas fa-file-pdf mr-2"></i>Lihat Borang
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                <?php elseif (in_array($complaint['workflow_status'], ['dimajukan_pegawai_pelulus', 'diluluskan', 'selesai'])): ?>
                <div class="bg-green-50 border border-green-200 rounded-xl p-6">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-green-600 text-2xl mt-1"></i>
                        <div>
                            <h3 class="font-bold text-green-800 mb-2">Borang Telah Dimajukan</h3>
                            <p class="text-green-700">
                                Borang Kerosakan Aset Alih telah disemak dan dimajukan kepada Pegawai Pelulus untuk keputusan.
                            </p>
                            <div class="mt-4">
                                <a href="generate_borang.php?id=<?php echo $complaint_id; ?>" target="_blank"
                                   class="inline-block px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                    <i class="fas fa-file-pdf mr-2"></i>Lihat Borang Kerosakan Aset
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Workflow Status Card -->
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

                <!-- Reference Info -->
                <?php if (!empty($complaint['no_rujukan_fail'])): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="font-bold text-gray-800 mb-4">Maklumat Rujukan</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-gray-600">No. Rujukan Fail</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['no_rujukan_fail']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Timeline -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="font-bold text-gray-800 mb-4">Maklumat Masa</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-gray-600">Tarikh Aduan Dibuat</label>
                            <p class="font-medium"><?php echo date('d/m/Y H:i', strtotime($complaint['created_at'])); ?></p>
                        </div>
                        <?php if (!empty($complaint['unit_aduan_verified_at'])): ?>
                        <div>
                            <label class="text-sm text-gray-600">Diterima dari Unit Aduan</label>
                            <p class="font-medium"><?php echo date('d/m/Y H:i', strtotime($complaint['unit_aduan_verified_at'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('borangAsetForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const actionButton = e.submitter;
            const action = actionButton.value;

            // Confirm before submitting
            if (action === 'forward_to_pelulus') {
                if (!confirm('Adakah anda pasti untuk menghantar borang ini kepada Pegawai Pelulus? Tindakan ini tidak boleh dibatalkan.')) {
                    return;
                }
            }

            formData.set('action', action);

            try {
                const response = await fetch('process_borang.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    if (action === 'save_draft') {
                        alert('Draf berjaya disimpan!');
                    } else {
                        alert('Borang berjaya dihantar kepada Pegawai Pelulus!');
                    }
                    window.location.reload();
                } else {
                    alert(result.message || 'Gagal memproses borang');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ralat sambungan. Sila cuba lagi.');
            }
        });
    </script>
</body>
</html>
