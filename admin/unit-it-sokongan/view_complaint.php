<?php
/**
 * Unit IT / Sokongan - View and Complete Complaint
 */

require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !isUnitITSokongan()) {
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
           bka.anggaran_kos_penyelenggaraan,
           bka.keputusan_ulasan,
           bka.keputusan_status,
           bka.keputusan_tarikh,
           bka.keputusan_nama,
           uito.nama as assigned_officer_name,
           u_pelulus.nama_penuh as pelulus_name,
           u_completed.nama_penuh as completed_by_name
    FROM complaints c
    LEFT JOIN borang_kerosakan_aset bka ON c.id = bka.complaint_id
    LEFT JOIN unit_it_sokongan_officers uito ON c.unit_it_officer_id = uito.id
    LEFT JOIN users u_pelulus ON c.pegawai_pelulus_id = u_pelulus.id
    LEFT JOIN users u_completed ON c.unit_it_completed_by = u_completed.id
    WHERE c.id = ?
");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    redirect('complaints.php');
}

// Get attachments
$stmt = $db->prepare("SELECT * FROM attachments WHERE complaint_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$complaint_id]);
$attachments = $stmt->fetchAll();

$user = getUser();
$can_complete = ($complaint['workflow_status'] === 'diluluskan');
$is_approved = ($complaint['workflow_status'] === 'diluluskan');
$is_waiting_approval = in_array($complaint['workflow_status'], ['dimajukan_unit_aset', 'dalam_semakan_unit_aset', 'dimajukan_pegawai_pelulus']);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Aduan #<?php echo htmlspecialchars($complaint['ticket_number']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
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
                    <span class="font-semibold text-lg">Lihat Aduan</span>
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
                            $status_colors = [
                                'dimajukan_unit_aset' => 'bg-blue-100 text-blue-800',
                                'dalam_semakan_unit_aset' => 'bg-blue-100 text-blue-800',
                                'dimajukan_pegawai_pelulus' => 'bg-yellow-100 text-yellow-800',
                                'diluluskan' => 'bg-green-100 text-green-800',
                                'selesai' => 'bg-gray-100 text-gray-800'
                            ];
                            $status_labels = [
                                'dimajukan_unit_aset' => 'Menunggu Kelulusan',
                                'dalam_semakan_unit_aset' => 'Dalam Semakan',
                                'dimajukan_pegawai_pelulus' => 'Menunggu Kelulusan',
                                'diluluskan' => 'Diluluskan - Perlu Tindakan',
                                'selesai' => 'Selesai'
                            ];
                            $color = $status_colors[$complaint['workflow_status']] ?? 'bg-gray-100 text-gray-800';
                            $label = $status_labels[$complaint['workflow_status']] ?? $complaint['workflow_status'];
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

                <!-- Waiting for Approval Notice -->
                <?php if ($is_waiting_approval): ?>
                <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-6">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-hourglass-half text-yellow-600 text-2xl mt-1"></i>
                        <div class="flex-1">
                            <h3 class="font-bold text-yellow-800 mb-2">Menunggu Kelulusan</h3>
                            <p class="text-yellow-700 mb-2">
                                Aduan ini telah ditugaskan kepada anda, tetapi masih menunggu kelulusan daripada Pegawai Pelulus.
                            </p>
                            <p class="text-sm text-yellow-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                Anda hanya boleh mengambil tindakan selepas aduan ini diluluskan oleh Pegawai Pelulus.
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Complaint Details -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Maklumat Pengadu & Aset</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-600">Nama Pengadu</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['nama_pengadu']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Email</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['email']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Bahagian</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['bahagian']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">No. Telefon</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['no_telefon'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Jenis Aset</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['jenis_aset'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">No. Pendaftaran Aset</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['no_pendaftaran_aset'] ?? '-'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Attachments -->
                <?php if (count($attachments) > 0): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Lampiran</h2>
                    <div class="space-y-3">
                        <?php foreach ($attachments as $attachment): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-file text-blue-600 text-2xl"></i>
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($attachment['file_original_name']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo formatFileSize($attachment['file_size']); ?></p>
                                </div>
                            </div>
                            <a href="<?php echo htmlspecialchars($attachment['file_path']); ?>"
                               target="_blank"
                               class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                <i class="fas fa-download mr-2"></i>Muat Turun
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Mark as Complete Form -->
                <?php if ($can_complete): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <span class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-check text-green-600"></i>
                        </span>
                        Tandakan Sebagai Selesai
                    </h2>

                    <form id="completionForm" class="space-y-6">
                        <input type="hidden" name="complaint_id" value="<?php echo $complaint_id; ?>">

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Catatan Tindakan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="catatan" rows="5" required
                                      placeholder="Nyatakan tindakan yang telah diambil untuk menyelesaikan aduan ini..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                            <p class="text-xs text-gray-500 mt-1">Catatan akan dipaparkan dalam sejarah aduan</p>
                        </div>

                        <div class="w-full md:w-1/2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Tarikh Selesai <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tarikh_selesai" required
                                   value="<?php echo date('Y-m-d'); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                                <div>
                                    <p class="text-sm text-blue-800 font-medium">Perhatian</p>
                                    <p class="text-xs text-blue-600 mt-1">
                                        Setelah menandakan sebagai selesai, aduan ini akan ditutup dan pengguna akan menerima notifikasi.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4 pt-4 border-t border-gray-200">
                            <button type="submit"
                                    class="flex-1 px-8 py-4 gradient-bg text-white rounded-lg hover:opacity-90 transition font-semibold text-lg">
                                <i class="fas fa-check mr-2"></i>Tandakan Sebagai Selesai
                            </button>
                            <a href="complaints.php"
                               class="px-8 py-4 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition font-semibold">
                                <i class="fas fa-times mr-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
                <?php else: ?>
                <!-- Completion Info (Read-only) -->
                <?php if (!empty($complaint['unit_it_completed_at'])): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <span class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </span>
                        Aduan Selesai
                    </h2>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-sm text-green-800">
                            Aduan ini telah ditandakan sebagai selesai oleh <?php echo htmlspecialchars($complaint['completed_by_name'] ?? 'Unknown'); ?>
                            pada <?php echo date('d/m/Y H:i', strtotime($complaint['unit_it_completed_at'])); ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Assignment Info -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="font-bold text-gray-800 mb-4">Maklumat Tugasan</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-gray-600">Ditugaskan Kepada</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['assigned_officer_name']); ?></p>
                        </div>
                        <?php if (!empty($complaint['keputusan_nama'])): ?>
                        <div>
                            <label class="text-sm text-gray-600">Diluluskan Oleh</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['keputusan_nama']); ?></p>
                        </div>
                        <?php endif; ?>
                        <div>
                            <label class="text-sm text-gray-600">Tarikh Ditugaskan</label>
                            <p class="font-medium"><?php echo date('d/m/Y H:i', strtotime($complaint['unit_it_assigned_at'])); ?></p>
                        </div>
                        <?php if ($is_approved && !empty($complaint['keputusan_tarikh'])): ?>
                        <div>
                            <label class="text-sm text-gray-600">Tarikh Diluluskan</label>
                            <p class="font-medium"><?php echo date('d/m/Y', strtotime($complaint['keputusan_tarikh'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Status Timeline -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="font-bold text-gray-800 mb-4">Sejarah Status</h3>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mt-2 mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">Aduan Dibuat</p>
                                <p class="text-xs text-gray-500"><?php echo date('d/m/Y H:i', strtotime($complaint['created_at'])); ?></p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-2 h-2 bg-purple-500 rounded-full mt-2 mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">Ditugaskan kepada Pegawai</p>
                                <p class="text-xs text-gray-500"><?php echo date('d/m/Y H:i', strtotime($complaint['unit_it_assigned_at'])); ?></p>
                            </div>
                        </div>
                        <?php if ($is_approved && !empty($complaint['keputusan_tarikh'])): ?>
                        <div class="flex items-start">
                            <div class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">Diluluskan</p>
                                <p class="text-xs text-gray-500"><?php echo date('d/m/Y', strtotime($complaint['keputusan_tarikh'])); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($complaint['unit_it_completed_at'])): ?>
                        <div class="flex items-start">
                            <div class="w-2 h-2 bg-green-600 rounded-full mt-2 mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">Selesai</p>
                                <p class="text-xs text-gray-500"><?php echo date('d/m/Y H:i', strtotime($complaint['unit_it_completed_at'])); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('completionForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!confirm('Adakah anda pasti untuk menandakan aduan ini sebagai SELESAI? Tindakan ini tidak boleh dibatalkan.')) {
                return;
            }

            const formData = new FormData(this);

            try {
                const response = await fetch('process_completion.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    window.location.href = 'complaints.php';
                } else {
                    alert('Ralat: ' + result.message);
                }
            } catch (error) {
                alert('Ralat: ' + error.message);
            }
        });
    </script>
</body>
</html>
