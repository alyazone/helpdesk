<?php
/**
 * Unit Aduan Dalaman - View and Process Complaint
 */

require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !isUnitAduanDalaman()) {
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
           u.nama_penuh as verified_by_name,
           uao.nama as dimajukan_ke_nama,
           o.nama as officer_name
    FROM complaints c
    LEFT JOIN users u ON c.unit_aduan_verified_by = u.id
    LEFT JOIN unit_aset_officers uao ON c.dimajukan_ke = uao.id
    LEFT JOIN officers o ON c.officer_id = o.id
    WHERE c.id = ?
");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    redirect('complaints.php');
}

// Get dokumen unit aduan if exists
$stmt = $db->prepare("SELECT * FROM dokumen_unit_aduan WHERE complaint_id = ?");
$stmt->execute([$complaint_id]);
$dokumen = $stmt->fetch();

// Get all Unit Aset Officers for dropdown
$stmt = $db->query("SELECT * FROM unit_aset_officers WHERE status = 'aktif' ORDER BY nama");
$unit_aset_officers = $stmt->fetchAll();

// Get Unit IT officers (for assignment if approved)
$stmt = $db->prepare("SELECT * FROM unit_it_sokongan_officers WHERE status = 'aktif' ORDER BY nama ASC");
$stmt->execute();
$unit_it_officers = $stmt->fetchAll();

// Get attachments
$stmt = $db->prepare("SELECT * FROM attachments WHERE complaint_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$complaint_id]);
$attachments = $stmt->fetchAll();

$user = getUser();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Aduan #<?php echo htmlspecialchars($complaint['ticket_number']); ?> - Unit Aduan Dalaman</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
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
                                'baru' => 'bg-yellow-100 text-yellow-800',
                                'disahkan_unit_aduan' => 'bg-green-100 text-green-800',
                                'dimajukan_unit_aset' => 'bg-purple-100 text-purple-800',
                            ];
                            $workflow_labels = [
                                'baru' => 'Baru',
                                'disahkan_unit_aduan' => 'Disahkan Unit Aduan',
                                'dimajukan_unit_aset' => 'Dimajukan ke Unit Aset',
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

                <!-- Asset Details (if applicable) -->
                <?php if (!empty($complaint['jenis_aset'])): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Maklumat Aset</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-600">Jenis Aset</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['jenis_aset']); ?></p>
                        </div>
                        <?php if (!empty($complaint['no_pendaftaran_aset'])): ?>
                        <div>
                            <label class="text-sm text-gray-600">No. Pendaftaran Aset</label>
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
                                <i class="fas fa-file text-purple-600 text-xl"></i>
                                <div>
                                    <p class="font-medium"><?php echo htmlspecialchars($attachment['file_original_name']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo formatFileSize($attachment['file_size']); ?></p>
                                </div>
                            </div>
                            <a href="<?php
                                    // Convert absolute path to web path
                                    $web_path = str_replace('\\', '/', $attachment['file_path']);
                                    if (strpos($web_path, '/uploads/') !== false) {
                                    $web_path = '../' . substr($web_path, strpos($web_path, 'uploads/'));
                                    }
                                    echo htmlspecialchars($web_path);
                                    ?>" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                                    <i class="fas fa-download mr-2"></i>Muat Turun
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Unit Aduan Dalaman Form -->
                <?php if ($complaint['workflow_status'] === 'baru' || $complaint['workflow_status'] === 'disahkan_unit_aduan'): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Untuk Kegunaan Pejabat Sahaja</h2>
                    <form id="unitAduanForm" class="space-y-4">
                        <input type="hidden" name="complaint_id" value="<?php echo $complaint_id; ?>">

                        <!-- No. Rujukan Fail -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">No. Rujukan Fail</label>
                            <input type="text" name="no_rujukan_fail"
                                   value="<?php echo htmlspecialchars($complaint['ticket_number']); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                   readonly>
                        </div>

                        <!-- Dimajukan ke (Unit Aset Officer) -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Dimajukan ke <span class="text-red-500">*</span>
                            </label>
                            <select name="dimajukan_ke" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="">-- Pilih Pegawai Unit Aset --</option>
                                <?php foreach ($unit_aset_officers as $officer): ?>
                                <option value="<?php echo $officer['id']; ?>"
                                        <?php echo (isset($complaint['dimajukan_ke']) && $complaint['dimajukan_ke'] == $officer['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($officer['nama']); ?>
                                    <?php if (!empty($officer['jawatan'])): ?>
                                        (<?php echo htmlspecialchars($officer['jawatan']); ?>)
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Tindakan Susulan -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Tindakan / Kesimpulan <span class="text-red-500">*</span>
                            </label>
                            <div class="mb-2 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-sm text-gray-700">
                                    <?php echo htmlspecialchars($complaint['keterangan'] ?? ''); ?>
                                </p>
                            </div>
                            <textarea name="tindakan_susulan" rows="4" required placeholder="Aduan telah diterima dan akan diambil tindakan segera..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"><?php echo htmlspecialchars($complaint['tindakan_susulan'] ?? ''); ?></textarea>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-4 pt-4">
                            <button type="submit" name="action" value="verify"
                                    class="px-6 py-3 gradient-bg text-white rounded-lg hover:opacity-90 transition">
                                <i class="fas fa-check-circle mr-2"></i>Sahkan & Majukan ke Unit Aset
                            </button>
                            <?php if ($dokumen || $complaint['workflow_status'] === 'disahkan_unit_aduan'): ?>
                            <a href="generate_dokumen.php?id=<?php echo $complaint_id; ?>" target="_blank"
                               class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-file-pdf mr-2"></i>Jana Dokumen Unit Aduan
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                <?php elseif ($complaint['workflow_status'] === 'diluluskan'): ?>
                <!-- Unit IT Officer Assignment (for approved complaints) -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Untuk Kegunaan Pejabat Sahaja</h2>
                    <p class="text-sm text-gray-600 mb-4">Aduan ini telah diluluskan. Sila pilih pegawai untuk tindakan susulan.</p>

                    <form id="assignItOfficerForm" class="space-y-4">
                        <input type="hidden" name="complaint_id" value="<?php echo $complaint_id; ?>">

                        <!-- No. Rujukan Fail -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">No. Rujukan Fail</label>
                            <input type="text" name="no_rujukan_fail"
                                   value="<?php echo htmlspecialchars($complaint['ticket_number']); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                   readonly>
                        </div>

                        <!-- Unit IT Officer Assignment -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Dimajukan ke <span class="text-red-500">*</span>
                            </label>
                            <select name="unit_it_officer_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="">-- Pilih Pegawai Unit IT / Sokongan --</option>
                                <?php foreach ($unit_it_officers as $officer): ?>
                                <option value="<?php echo $officer['id']; ?>">
                                    <?php echo htmlspecialchars($officer['nama']); ?>
                                    <?php if (!empty($officer['jawatan'])): ?>
                                        (<?php echo htmlspecialchars($officer['jawatan']); ?>)
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Pegawai yang dipilih akan menerima tugasan untuk melaksanakan tindakan yang diperlukan
                            </p>
                        </div>

                        <!-- Tindakan Susulan -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Tindakan / Kesimpulan <span class="text-red-500">*</span>
                            </label>
                            <div class="mb-2 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-sm text-gray-700">
                                    <?php echo htmlspecialchars($complaint['keterangan'] ?? ''); ?>
                                </p>
                            </div>
                            <textarea name="tindakan_susulan" rows="4" required placeholder="Aduan telah diterima dan akan diambil tindakan segera..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"></textarea>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-4 pt-4">
                            <button type="submit" name="action" value="assign_it_officer"
                                    class="px-6 py-3 gradient-bg text-white rounded-lg hover:opacity-90 transition">
                                <i class="fas fa-check-circle mr-2"></i>Sahkan & Majukan ke Unit Aset
                            </button>
                        </div>
                    </form>
                </div>
                <?php elseif ($complaint['workflow_status'] === 'dimajukan_unit_aset' || in_array($complaint['workflow_status'], ['dalam_semakan_unit_aset', 'dimajukan_pegawai_pelulus', 'selesai'])): ?>
                <div class="bg-green-50 border border-green-200 rounded-xl p-6">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-green-600 text-2xl mt-1"></i>
                        <div>
                            <h3 class="font-bold text-green-800 mb-2">Aduan Telah Dimajukan</h3>
                            <p class="text-green-700">
                                Aduan ini telah disahkan dan dimajukan kepada <strong><?php echo htmlspecialchars($complaint['dimajukan_ke_nama']); ?></strong>
                                di Unit Aset untuk tindakan seterusnya.
                            </p>
                            <?php if (!empty($complaint['unit_aduan_verified_at'])): ?>
                            <p class="text-sm text-green-600 mt-2">
                                Dimajukan pada: <?php echo date('d/m/Y H:i', strtotime($complaint['unit_aduan_verified_at'])); ?>
                            </p>
                            <?php endif; ?>
                            <div class="mt-4">
                                <a href="generate_dokumen.php?id=<?php echo $complaint_id; ?>" target="_blank"
                                   class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    <i class="fas fa-file-pdf mr-2"></i>Lihat Dokumen Unit Aduan
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

                <!-- Timeline -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="font-bold text-gray-800 mb-4">Maklumat Masa</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-gray-600">Tarikh Dibuat</label>
                            <p class="font-medium"><?php echo date('d/m/Y H:i', strtotime($complaint['created_at'])); ?></p>
                        </div>
                        <?php if (!empty($complaint['unit_aduan_verified_at'])): ?>
                        <div>
                            <label class="text-sm text-gray-600">Tarikh Disahkan</label>
                            <p class="font-medium"><?php echo date('d/m/Y H:i', strtotime($complaint['unit_aduan_verified_at'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('unitAduanForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch('process_complaint.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert('Aduan berjaya disahkan dan dimajukan ke Unit Aset!');
                    window.location.reload();
                } else {
                    alert(result.message || 'Gagal memproses aduan');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ralat sambungan. Sila cuba lagi.');
            }
        });

        document.getElementById('assignItOfficerForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            if (!confirm('Adakah anda pasti untuk memajukan aduan ini kepada Pegawai Unit IT / Sokongan yang dipilih?')) {
                return;
            }

            try {
                const response = await fetch('assign_it_officer.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert('Aduan berjaya dimajukan ke Unit IT / Sokongan!');
                    window.location.reload();
                } else {
                    alert(result.message || 'Gagal memajukan aduan');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ralat sambungan. Sila cuba lagi.');
            }
        });
    </script>
</body>
</html>
