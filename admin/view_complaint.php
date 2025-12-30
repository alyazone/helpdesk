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

// Get workflow actions/documents
$stmt = $db->prepare("SELECT * FROM workflow_actions WHERE complaint_id = ? ORDER BY created_at DESC");
$stmt->execute([$complaint_id]);
$workflow_actions = $stmt->fetchAll();

// Get cost details from borang_kerosakan_aset
$stmt = $db->prepare("SELECT * FROM borang_kerosakan_aset WHERE complaint_id = ? LIMIT 1");
$stmt->execute([$complaint_id]);
$borang_aset = $stmt->fetch();

// Get internal complaint unit documents
$stmt = $db->prepare("SELECT * FROM dokumen_unit_aduan WHERE complaint_id = ? ORDER BY created_at DESC");
$stmt->execute([$complaint_id]);
$dokumen_unit_aduan = $stmt->fetchAll();

$user = getUser();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aduan/Cadangan » Kemaskini Maklumat - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }

        .tab-button {
            padding: 12px 24px;
            background: transparent;
            border-bottom: 3px solid transparent;
            color: #6B7280;
            font-weight: 500;
            transition: all 0.3s;
        }

        .tab-button:hover {
            color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .tab-button.active {
            color: #667eea;
            border-bottom-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        @media print {
            .no-print { display: none; }
            body { background: white; }
            .tab-content { display: block !important; }
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
                    <span class="font-semibold text-lg">Aduan/Cadangan » Kemaskini Maklumat</span>
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
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-3">
                <!-- Tabs -->
                <div class="bg-white rounded-t-xl shadow-md">
                    <div class="flex border-b overflow-x-auto">
                        <button class="tab-button active" onclick="switchTab(event, 'tab-aduan')">
                            Butiran Aduan
                        </button>
                        <button class="tab-button" onclick="switchTab(event, 'tab-aset')">
                            Butiran Aset
                        </button>
                        <button class="tab-button" onclick="switchTab(event, 'tab-pengadu')">
                            Butiran Pengadu
                        </button>
                        <button class="tab-button" onclick="switchTab(event, 'tab-kos')">
                            Butiran Kos
                        </button>
                        <button class="tab-button" onclick="switchTab(event, 'tab-lampiran')">
                            Lampiran
                        </button>
                        <button class="tab-button" onclick="switchTab(event, 'tab-sejarah')">
                            Sejarah Status
                        </button>
                        <button class="tab-button" onclick="switchTab(event, 'tab-dokumen')">
                            Dokumen/Laporan
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Butiran Aduan -->
                        <div id="tab-aduan" class="tab-content active">
                            <h3 class="text-xl font-bold text-gray-800 mb-6">Maklumat Aduan</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Perkara</label>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($complaint['perkara']); ?></p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis</label>
                                    <span class="px-3 py-1 rounded text-sm font-semibold <?php echo $complaint['jenis'] === 'aduan' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'; ?>">
                                        <?php echo ucfirst($complaint['jenis']); ?>
                                    </span>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan</label>
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <p class="text-gray-900 whitespace-pre-wrap"><?php echo htmlspecialchars($complaint['keterangan']); ?></p>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tarikh Dihantar</label>
                                    <p class="text-gray-900"><?php echo date('d F Y', strtotime($complaint['created_at'])); ?></p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                                    <?php
                                    $status_colors = [
                                        'pending' => 'bg-gray-100 text-gray-800',
                                        'dalam_pemeriksaan' => 'bg-blue-100 text-blue-800',
                                        'sedang_dibaiki' => 'bg-yellow-100 text-yellow-800',
                                        'selesai' => 'bg-green-100 text-green-800',
                                        'dibatalkan' => 'bg-red-100 text-red-800'
                                    ];
                                    $color = $status_colors[$complaint['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $color; ?>">
                                        <?php echo str_replace('_', ' ', ucfirst($complaint['status'])); ?>
                                    </span>
                                </div>

                                <?php if ($complaint['status'] === 'selesai' && !empty($complaint['rating'])): ?>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Maklum Balas Pengguna</label>
                                    <div class="p-4 bg-green-50 rounded-lg">
                                        <p class="text-lg font-semibold text-gray-800 mb-2">
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
                                        <?php if (!empty($complaint['feedback_comment'])): ?>
                                            <p class="text-gray-700 mt-2"><?php echo htmlspecialchars($complaint['feedback_comment']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Butiran Aset -->
                        <div id="tab-aset" class="tab-content">
                            <h3 class="text-xl font-bold text-gray-800 mb-6">Maklumat Aset</h3>

                            <?php if (!empty($complaint['jenis_aset'])): ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Aset</label>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($complaint['jenis_aset']); ?></p>
                                </div>

                                <?php if (!empty($complaint['no_pendaftaran_aset'])): ?>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">No. Pendaftaran Aset</label>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($complaint['no_pendaftaran_aset']); ?></p>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($complaint['pengguna_akhir'])): ?>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pengguna Akhir</label>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($complaint['pengguna_akhir']); ?></p>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($complaint['tarikh_kerosakan'])): ?>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tarikh Kerosakan</label>
                                    <p class="text-gray-900"><?php echo date('d F Y', strtotime($complaint['tarikh_kerosakan'])); ?></p>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($complaint['perihal_kerosakan'])): ?>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Perihal Kerosakan</label>
                                    <p class="text-gray-900">
                                        <?php echo htmlspecialchars($complaint['perihal_kerosakan']); ?>
                                        <?php if (!empty($complaint['perihal_kerosakan_value'])): ?>
                                            - <?php echo htmlspecialchars($complaint['perihal_kerosakan_value']); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($complaint['tingkat'])): ?>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tingkat</label>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($complaint['tingkat']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-box-open text-4xl mb-2"></i>
                                <p>Tiada maklumat aset untuk aduan ini</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Butiran Pengadu -->
                        <div id="tab-pengadu" class="tab-content">
                            <h3 class="text-xl font-bold text-gray-800 mb-6">Maklumat Pengadu</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Pengadu</label>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($complaint['nama_pengadu']); ?></p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($complaint['email']); ?></p>
                                </div>

                                <?php if (!empty($complaint['no_telefon'])): ?>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">No. Telefon</label>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($complaint['no_telefon']); ?></p>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($complaint['no_sambungan'])): ?>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">No. Sambungan</label>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($complaint['no_sambungan']); ?></p>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($complaint['jawatan'])): ?>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jawatan</label>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($complaint['jawatan']); ?></p>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($complaint['bahagian'])): ?>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Bahagian</label>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($complaint['bahagian']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Butiran Kos -->
                        <div id="tab-kos" class="tab-content">
                            <h3 class="text-xl font-bold text-gray-800 mb-6">Butiran Kos</h3>

                            <?php if (!empty($borang_aset)): ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <?php if (!empty($borang_aset['jumlah_kos_penyelenggaraan_terdahulu'])): ?>
                                <div class="p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                                    <label class="block text-sm font-semibold text-blue-700 mb-2">Jumlah Kos Penyelenggaraan Terdahulu</label>
                                    <p class="text-2xl font-bold text-blue-900">
                                        RM <?php echo number_format($borang_aset['jumlah_kos_penyelenggaraan_terdahulu'], 2); ?>
                                    </p>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($borang_aset['anggaran_kos_penyelenggaraan'])): ?>
                                <div class="p-4 bg-green-50 rounded-lg border-l-4 border-green-500">
                                    <label class="block text-sm font-semibold text-green-700 mb-2">Anggaran Kos Penyelenggaraan</label>
                                    <p class="text-2xl font-bold text-green-900">
                                        RM <?php echo number_format($borang_aset['anggaran_kos_penyelenggaraan'], 2); ?>
                                    </p>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($borang_aset['jumlah_kos_penyelenggaraan_terdahulu']) && !empty($borang_aset['anggaran_kos_penyelenggaraan'])): ?>
                                <div class="md:col-span-2 p-4 bg-purple-50 rounded-lg border-l-4 border-purple-500">
                                    <label class="block text-sm font-semibold text-purple-700 mb-2">Perbezaan Kos</label>
                                    <p class="text-2xl font-bold text-purple-900">
                                        RM <?php
                                        $difference = $borang_aset['anggaran_kos_penyelenggaraan'] - $borang_aset['jumlah_kos_penyelenggaraan_terdahulu'];
                                        echo number_format(abs($difference), 2);
                                        ?>
                                        <span class="text-base font-normal <?php echo $difference >= 0 ? 'text-red-600' : 'text-green-600'; ?>">
                                            (<?php echo $difference >= 0 ? '+' : '-'; ?><?php echo number_format(abs($difference / $borang_aset['jumlah_kos_penyelenggaraan_terdahulu'] * 100), 1); ?>%)
                                        </span>
                                    </p>
                                </div>
                                <?php endif; ?>

                                <?php
                                // Display other fields from borang_kerosakan_aset if available
                                $display_fields = [
                                    'nama_aset' => 'Nama Aset',
                                    'jenis_aset' => 'Jenis Aset',
                                    'no_siri' => 'No. Siri',
                                    'lokasi' => 'Lokasi',
                                    'status_aset' => 'Status Aset'
                                ];

                                foreach ($display_fields as $field => $label):
                                    if (!empty($borang_aset[$field])):
                                ?>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?php echo $label; ?></label>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($borang_aset[$field]); ?></p>
                                </div>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-money-bill-wave text-4xl mb-2"></i>
                                <p>Maklumat kos tidak tersedia untuk aduan ini</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Lampiran -->
                        <div id="tab-lampiran" class="tab-content">
                            <h3 class="text-xl font-bold text-gray-800 mb-6">Lampiran</h3>

                            <?php if (!empty($attachments)): ?>
                            <div class="space-y-3">
                                <?php foreach ($attachments as $attachment): ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                    <div class="flex items-center space-x-3">
                                        <i class="fas fa-file text-purple-600 text-2xl"></i>
                                        <div>
                                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($attachment['file_original_name']); ?></p>
                                            <p class="text-xs text-gray-500">
                                                <?php echo formatFileSize($attachment['file_size']); ?> •
                                                <?php echo date('d/m/Y H:i', strtotime($attachment['uploaded_at'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <a href="<?php echo htmlspecialchars($attachment['file_path']); ?>"
                                       target="_blank"
                                       class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                                        <i class="fas fa-download mr-2"></i>Muat Turun
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-paperclip text-4xl mb-2"></i>
                                <p>Tiada lampiran untuk aduan ini</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Sejarah Status -->
                        <div id="tab-sejarah" class="tab-content">
                            <h3 class="text-xl font-bold text-gray-800 mb-6">Sejarah Status</h3>

                            <?php if (!empty($status_history)): ?>
                            <div class="space-y-4">
                                <?php foreach ($status_history as $history): ?>
                                <div class="flex gap-4 p-4 bg-gray-50 rounded-lg">
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
                                            <?php echo date('d F Y, H:i', strtotime($history['created_at'])); ?>
                                            <?php if (!empty($history['changed_by_name'])): ?>
                                                oleh <?php echo htmlspecialchars($history['changed_by_name']); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-clock text-4xl mb-2"></i>
                                <p>Tiada sejarah status</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Dokumen/Laporan -->
                        <div id="tab-dokumen" class="tab-content">
                            <h3 class="text-xl font-bold text-gray-800 mb-6">Dokumen/Laporan</h3>

                            <div class="space-y-6">
                                <!-- Dokumen Aduan (from attachments) -->
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-700 mb-3 flex items-center">
                                        <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                                        Dokumen Aduan (Lampiran Asal)
                                    </h4>
                                    <?php if (!empty($attachments)): ?>
                                        <div class="space-y-3">
                                            <?php foreach ($attachments as $attachment): ?>
                                            <div class="p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center space-x-3">
                                                        <i class="fas fa-file text-blue-600 text-2xl"></i>
                                                        <div>
                                                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($attachment['file_original_name']); ?></p>
                                                            <p class="text-xs text-gray-500">
                                                                Dimuat naik pada <?php echo date('d/m/Y H:i', strtotime($attachment['uploaded_at'])); ?> •
                                                                <?php echo formatFileSize($attachment['file_size']); ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <a href="<?php
                                                        // Convert absolute path to web path
                                                        $web_path = str_replace('\\', '/', $attachment['file_path']);
                                                        if (strpos($web_path, '/uploads/') !== false) {
                                                            $web_path = '../' . substr($web_path, strpos($web_path, 'uploads/'));
                                                        }
                                                        echo htmlspecialchars($web_path);
                                                    ?>"
                                                       target="_blank"
                                                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                                        <i class="fas fa-download mr-2"></i>Muat Turun
                                                    </a>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="p-4 bg-gray-50 rounded-lg text-center text-gray-500">
                                            <i class="fas fa-file-alt text-gray-400 text-2xl mb-2"></i>
                                            <p class="text-sm">Tiada dokumen aduan</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Dokumen Unit Aduan Dalaman -->
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-700 mb-3 flex items-center">
                                        <i class="fas fa-folder text-purple-600 mr-2"></i>
                                        Dokumen Unit Aduan Dalaman
                                    </h4>
                                    <?php if (!empty($dokumen_unit_aduan)): ?>
                                        <div class="space-y-3">
                                            <?php foreach ($dokumen_unit_aduan as $dokumen): ?>
                                            <div class="p-4 bg-purple-50 rounded-lg border-l-4 border-purple-500">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center space-x-3">
                                                        <i class="fas fa-file-pdf text-purple-600 text-2xl"></i>
                                                        <div>
                                                            <p class="font-medium text-gray-900">Dokumen Unit Aduan</p>
                                                            <p class="text-xs text-gray-500">
                                                                Dimuat naik pada <?php echo date('d/m/Y H:i', strtotime($dokumen['created_at'])); ?>
                                                                <?php if (!empty($dokumen['no_rujukan_fail'])): ?>
                                                                • Rujukan: <?php echo htmlspecialchars($dokumen['no_rujukan_fail']); ?>
                                                                <?php endif; ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <a href="unit-aduan-dalaman/generate_dokumen.php?id=<?php echo $complaint_id; ?>"
                                                       target="_blank"
                                                       class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                                                        <i class="fas fa-eye mr-2"></i>Papar
                                                    </a>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="p-4 bg-gray-50 rounded-lg text-center text-gray-500">
                                            <i class="fas fa-folder text-gray-400 text-2xl mb-2"></i>
                                            <p class="text-sm">Tiada dokumen unit aduan dalaman</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Borang Unit Aset -->
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-700 mb-3 flex items-center">
                                        <i class="fas fa-clipboard-check text-green-600 mr-2"></i>
                                        Borang Unit Aset
                                    </h4>
                                    <?php if (!empty($borang_aset)): ?>
                                        <div class="p-4 bg-green-50 rounded-lg border-l-4 border-green-500">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <i class="fas fa-file-pdf text-green-600 text-2xl"></i>
                                                    <div>
                                                        <p class="font-medium text-gray-900">Borang Kerosakan Aset</p>
                                                        <p class="text-xs text-gray-500">
                                                            Dimuat naik pada <?php echo date('d/m/Y H:i', strtotime($borang_aset['created_at'])); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <a href="unit-aset/generate_borang.php?id=<?php echo $complaint_id; ?>"
                                                   target="_blank"
                                                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                                    <i class="fas fa-eye mr-2"></i>Papar
                                                </a>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="p-4 bg-gray-50 rounded-lg text-center text-gray-500">
                                            <i class="fas fa-clipboard-check text-gray-400 text-2xl mb-2"></i>
                                            <p class="text-sm">Tiada borang unit aset</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Workflow Actions Documents (if any) -->
                                <?php if (!empty($workflow_actions) && array_filter($workflow_actions, function($a) { return !empty($a['dokumen_path']); })): ?>
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-700 mb-3 flex items-center">
                                        <i class="fas fa-stream text-orange-600 mr-2"></i>
                                        Dokumen Workflow Lain
                                    </h4>
                                    <div class="space-y-3">
                                        <?php foreach ($workflow_actions as $action): ?>
                                            <?php if (!empty($action['dokumen_path'])): ?>
                                            <div class="p-4 bg-orange-50 rounded-lg border-l-4 border-orange-500">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center space-x-3">
                                                        <i class="fas fa-file-pdf text-orange-600 text-2xl"></i>
                                                        <div>
                                                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($action['action_type'] ?? 'Dokumen Workflow'); ?></p>
                                                            <p class="text-xs text-gray-500">
                                                                <?php if (!empty($action['created_at'])): ?>
                                                                    <?php echo date('d/m/Y H:i', strtotime($action['created_at'])); ?>
                                                                <?php endif; ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <a href="<?php echo htmlspecialchars($action['dokumen_path']); ?>"
                                                       target="_blank"
                                                       class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition">
                                                        <i class="fas fa-download mr-2"></i>Muat Turun
                                                    </a>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Garis Masa -->
                <div class="bg-white rounded-xl shadow-md p-6 no-print">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Garis Masa</h3>

                    <div class="space-y-4">
                        <?php if (!empty($complaint['rating'])): ?>
                        <div class="border-l-4 border-green-500 pl-4">
                            <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded mb-2">
                                Bagi
                            </span>
                            <p class="text-sm text-gray-700">
                                oleh <strong><?php echo htmlspecialchars($complaint['nama_pengadu']); ?></strong>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                <?php echo !empty($complaint['feedback_comment']) ? htmlspecialchars(substr($complaint['feedback_comment'], 0, 50)) . '...' : 'Tiada komen'; ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <?php
                        // Show recent status history in sidebar
                        $recent_history = array_slice($status_history, 0, 3);
                        foreach ($recent_history as $history):
                        ?>
                        <div class="border-l-4 border-blue-500 pl-4">
                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded mb-2">
                                <?php echo date('d M Y', strtotime($history['created_at'])); ?>
                            </span>
                            <p class="text-sm text-gray-700">
                                oleh <strong><?php echo htmlspecialchars($history['changed_by_name'] ?? 'System'); ?></strong>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                <?php echo ucfirst(str_replace('_', ' ', $history['status'])); ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <button class="w-full mt-4 px-4 py-2 text-purple-600 hover:bg-purple-50 rounded-lg transition text-sm font-medium">
                        Papar senarai aduan
                    </button>
                </div>

                <!-- Action Buttons -->
                <div class="bg-white rounded-xl shadow-md p-6 no-print">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Tindakan</h3>

                    <div class="space-y-3">
                        <button onclick="deleteComplaint(<?php echo $complaint['id']; ?>, '<?php echo htmlspecialchars($complaint['ticket_number']); ?>')"
                                class="w-full px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                            <i class="fas fa-trash mr-2"></i>Padam Aduan
                        </button>

                        <button onclick="window.print()"
                                class="w-full px-4 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-medium">
                            <i class="fas fa-print mr-2"></i>Cetak
                        </button>

                        <a href="complaints.php"
                           class="block w-full px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-medium text-center">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Senarai
                        </a>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-md p-6">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-white text-2xl"></i>
                        </div>
                        <p class="text-sm text-purple-700 mb-1">No. Tiket</p>
                        <p class="text-2xl font-bold text-purple-900"><?php echo htmlspecialchars($complaint['ticket_number']); ?></p>

                        <?php if (!empty($complaint['officer_name'])): ?>
                        <div class="mt-4 pt-4 border-t border-purple-200">
                            <p class="text-xs text-purple-700 mb-2">Pegawai Bertugas</p>
                            <p class="text-sm font-semibold text-purple-900"><?php echo htmlspecialchars($complaint['officer_name']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(event, tabId) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.classList.remove('active');
            });

            // Remove active class from all buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.classList.remove('active');
            });

            // Show selected tab content
            document.getElementById(tabId).classList.add('active');

            // Add active class to clicked button
            event.currentTarget.classList.add('active');
        }

        async function deleteComplaint(complaintId, ticketNumber) {
            if (!confirm(`Adakah anda pasti mahu memadam aduan "${ticketNumber}"?\n\nAmaran: Tindakan ini tidak boleh dibatalkan dan semua data berkaitan (lampiran, sejarah status) akan turut dipadam!`)) {
                return;
            }

            // Double confirmation for critical action
            if (!confirm('Pengesahan terakhir: Padam aduan ini secara kekal?')) {
                return;
            }

            try {
                const response = await fetch('../api/admin/delete_complaint.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `complaint_id=${complaintId}`
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
        }
    </script>
</body>
</html>
