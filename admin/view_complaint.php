<?php
/**
 * Admin - View Complaint Details
 */

require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.html?redirect=admin');
}

$db = getDB();
$complaint_id = intval($_GET['id'] ?? 0);

if ($complaint_id <= 0) {
    redirect('complaints.php');
}

// Get complaint details
$stmt = $db->prepare("
    SELECT c.*, o.nama as officer_name, o.email as officer_email,
           u.nama_penuh as user_nama, u.email as user_email
    FROM complaints c
    LEFT JOIN officers o ON c.officer_id = o.id
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.id = ?
");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    redirect('complaints.php');
}

// Get status history
$stmt = $db->prepare("
    SELECT csh.*, u.nama_penuh as changed_by_name
    FROM complaint_status_history csh
    LEFT JOIN users u ON csh.created_by = u.id
    WHERE csh.complaint_id = ?
    ORDER BY csh.created_at DESC
");
$stmt->execute([$complaint_id]);
$status_history = $stmt->fetchAll();

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
    <title>Lihat Aduan #<?php echo htmlspecialchars($complaint['ticket_number']); ?> - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        @media print {
            .no-print { display: none; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="gradient-bg text-white shadow-lg no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <a href="complaints.php" class="flex items-center space-x-2 hover:opacity-80">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali</span>
                    </a>
                    <span class="font-semibold text-lg">Butiran Aduan</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="hover:opacity-80">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                    <span class="text-sm"><?php echo htmlspecialchars($user['nama']); ?></span>
                    <a href="../api/logout.php" class="px-4 py-2 bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30 transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Log Keluar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Action Buttons -->
        <div class="mb-6 flex gap-4 no-print">
            <a href="edit_complaint.php?id=<?php echo $complaint['id']; ?>"
               class="px-6 py-3 gradient-bg text-white rounded-lg hover:opacity-90 transition">
                <i class="fas fa-edit mr-2"></i>Edit Aduan
            </a>
            <button onclick="window.print()" class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-print mr-2"></i>Cetak
            </button>
        </div>

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
                            $jenis_class = $complaint['jenis'] === 'aduan' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800';
                            ?>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $jenis_class; ?>">
                                <?php echo ucfirst($complaint['jenis']); ?>
                            </span>
                            <?php
                            $priority_colors = [
                                'rendah' => 'bg-gray-100 text-gray-800',
                                'sederhana' => 'bg-blue-100 text-blue-800',
                                'tinggi' => 'bg-orange-100 text-orange-800',
                                'kritikal' => 'bg-red-100 text-red-800'
                            ];
                            $priority_color = $priority_colors[$complaint['priority']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $priority_color; ?>">
                                Keutamaan: <?php echo ucfirst($complaint['priority']); ?>
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
                            <label class="text-sm text-gray-600">No. Sambungan</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['no_sambungan'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Jawatan</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['jawatan'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Bahagian</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['bahagian'] ?? '-'); ?></p>
                        </div>
                        <?php if (!empty($complaint['tingkat'])): ?>
                        <div>
                            <label class="text-sm text-gray-600">Tingkat</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['tingkat']); ?></p>
                        </div>
                        <?php endif; ?>
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
                        <?php if (!empty($complaint['pengguna_akhir'])): ?>
                        <div>
                            <label class="text-sm text-gray-600">Pengguna Akhir</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['pengguna_akhir']); ?></p>
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
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['perihal_kerosakan']); ?>
                                <?php if (!empty($complaint['perihal_kerosakan_value'])): ?>
                                    - <?php echo htmlspecialchars($complaint['perihal_kerosakan_value']); ?>
                                <?php endif; ?>
                            </p>
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
                            <a href="<?php echo htmlspecialchars($attachment['file_path']); ?>"
                               target="_blank"
                               class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition no-print">
                                <i class="fas fa-download mr-2"></i>Muat Turun
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Feedback (if completed) -->
                <?php if ($complaint['status'] === 'selesai' && !empty($complaint['rating'])): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Maklum Balas Pengguna</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-gray-600">Penilaian</label>
                            <p class="font-medium text-lg">
                                <?php
                                $rating_labels = [
                                    'cemerlang' => '⭐⭐⭐⭐⭐ Cemerlang',
                                    'baik' => '⭐⭐⭐⭐ Baik',
                                    'memuaskan' => '⭐⭐⭐ Memuaskan',
                                    'tidak_memuaskan' => '⭐⭐ Tidak Memuaskan'
                                ];
                                echo $rating_labels[$complaint['rating']] ?? $complaint['rating'];
                                ?>
                            </p>
                        </div>
                        <?php if (!empty($complaint['feedback_comment'])): ?>
                        <div>
                            <label class="text-sm text-gray-600">Komen</label>
                            <p class="font-medium"><?php echo htmlspecialchars($complaint['feedback_comment']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Status History -->
                <?php if (!empty($status_history)): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Sejarah Status</h2>
                    <div class="space-y-4">
                        <?php foreach ($status_history as $history): ?>
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-history text-purple-600"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800"><?php echo ucfirst(str_replace('_', ' ', $history['status'])); ?></p>
                                <?php if (!empty($history['keterangan'])): ?>
                                <p class="text-gray-700 text-sm mt-1"><?php echo htmlspecialchars($history['keterangan']); ?></p>
                                <?php endif; ?>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?php echo date('d/m/Y H:i', strtotime($history['created_at'])); ?>
                                    <?php if (!empty($history['changed_by_name'])): ?>
                                        oleh <?php echo htmlspecialchars($history['changed_by_name']); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
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
                    <h3 class="font-bold text-gray-800 mb-4">Status Semasa</h3>
                    <?php
                    $status_colors = [
                        'pending' => 'bg-gray-100 text-gray-800',
                        'dalam_pemeriksaan' => 'bg-blue-100 text-blue-800',
                        'sedang_dibaiki' => 'bg-yellow-100 text-yellow-800',
                        'selesai' => 'bg-green-100 text-green-800',
                        'dibatalkan' => 'bg-red-100 text-red-800'
                    ];
                    $status_labels = [
                        'pending' => 'Pending',
                        'dalam_pemeriksaan' => 'Dalam Pemeriksaan',
                        'sedang_dibaiki' => 'Sedang Dibaiki',
                        'selesai' => 'Selesai',
                        'dibatalkan' => 'Dibatalkan'
                    ];
                    $color = $status_colors[$complaint['status']] ?? 'bg-gray-100 text-gray-800';
                    $label = $status_labels[$complaint['status']] ?? $complaint['status'];
                    ?>
                    <div class="text-center p-4 rounded-lg <?php echo $color; ?>">
                        <p class="text-2xl font-bold"><?php echo $label; ?></p>
                    </div>

                    <?php if ($complaint['status'] !== 'selesai' && $complaint['status'] !== 'dibatalkan'): ?>
                    <div class="mt-4">
                        <label class="text-sm text-gray-600 mb-2 block">Kemajuan</label>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-gradient-to-r from-purple-500 to-purple-700 h-3 rounded-full"
                                 style="width: <?php echo $complaint['progress']; ?>%"></div>
                        </div>
                        <p class="text-right text-sm text-gray-600 mt-1"><?php echo $complaint['progress']; ?>%</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Officer Assignment -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="font-bold text-gray-800 mb-4">Pegawai Bertugas</h3>
                    <?php if (!empty($complaint['officer_name'])): ?>
                    <div class="flex items-center space-x-3 p-3 bg-purple-50 rounded-lg">
                        <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                            <?php echo strtoupper(substr($complaint['officer_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($complaint['officer_name']); ?></p>
                            <?php if (!empty($complaint['officer_email'])): ?>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($complaint['officer_email']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <p class="text-gray-500 text-center py-4">Belum ditugaskan</p>
                    <?php endif; ?>
                </div>

                <!-- Timeline -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="font-bold text-gray-800 mb-4">Maklumat Masa</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-gray-600">Tarikh Dibuat</label>
                            <p class="font-medium"><?php echo date('d/m/Y H:i', strtotime($complaint['created_at'])); ?></p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Tarikh Kemaskini</label>
                            <p class="font-medium"><?php echo date('d/m/Y H:i', strtotime($complaint['updated_at'])); ?></p>
                        </div>
                        <?php if (!empty($complaint['completed_at'])): ?>
                        <div>
                            <label class="text-sm text-gray-600">Tarikh Selesai</label>
                            <p class="font-medium"><?php echo date('d/m/Y H:i', strtotime($complaint['completed_at'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
