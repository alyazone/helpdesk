<?php
/**
 * Admin - Edit/Update Complaint
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
$stmt = $db->prepare("SELECT * FROM complaints WHERE id = ?");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    redirect('complaints.php');
}

// Get all officers
$stmt = $db->query("SELECT * FROM officers WHERE status = 'bertugas' ORDER BY nama ASC");
$officers = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = sanitize($_POST['status']);
    $priority = sanitize($_POST['priority']);
    $officer_id = intval($_POST['officer_id']) ?: null;
    $progress = intval($_POST['progress']);
    $keterangan = sanitize($_POST['keterangan'] ?? '');

    try {
        $db->beginTransaction();

        // Update complaint
        $update_fields = [
            'status' => $status,
            'priority' => $priority,
            'officer_id' => $officer_id,
            'progress' => $progress
        ];

        // Set completed_at if status is selesai
        if ($status === 'selesai') {
            $update_fields['completed_at'] = date('Y-m-d H:i:s');
            $update_fields['progress'] = 100;
        }

        $sql = "UPDATE complaints SET ";
        $sql_parts = [];
        $params = [];
        foreach ($update_fields as $field => $value) {
            $sql_parts[] = "$field = ?";
            $params[] = $value;
        }
        $sql .= implode(', ', $sql_parts);
        $sql .= " WHERE id = ?";
        $params[] = $complaint_id;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        // Add to status history if status changed
        if ($status !== $complaint['status']) {
            $stmt = $db->prepare("
                INSERT INTO complaint_status_history (complaint_id, status, keterangan, created_by)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $complaint_id,
                $status,
                $keterangan,
                $_SESSION['user_id']
            ]);
        }

        $db->commit();

        $_SESSION['success_message'] = 'Aduan berjaya dikemaskini';
        redirect("view_complaint.php?id={$complaint_id}");
    } catch (Exception $e) {
        $db->rollBack();
        $error_message = 'Ralat: ' . $e->getMessage();
    }
}

$user = getUser();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Aduan #<?php echo htmlspecialchars($complaint['ticket_number']); ?> - Admin</title>
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
                    <a href="view_complaint.php?id=<?php echo $complaint_id; ?>" class="flex items-center space-x-2 hover:opacity-80">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali</span>
                    </a>
                    <span class="font-semibold text-lg">Edit Aduan</span>
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

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if (isset($error_message)): ?>
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Edit Aduan</h1>
                <p class="text-gray-600">Tiket #<?php echo htmlspecialchars($complaint['ticket_number']); ?></p>
                <p class="text-gray-700 mt-2"><?php echo htmlspecialchars($complaint['perkara']); ?></p>
            </div>

            <form method="POST" class="space-y-6">
                <!-- Status -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="pending" <?php echo $complaint['status'] === 'pending' ? 'selected' : ''; ?>>
                            Pending
                        </option>
                        <option value="dalam_pemeriksaan" <?php echo $complaint['status'] === 'dalam_pemeriksaan' ? 'selected' : ''; ?>>
                            Dalam Pemeriksaan
                        </option>
                        <option value="sedang_dibaiki" <?php echo $complaint['status'] === 'sedang_dibaiki' ? 'selected' : ''; ?>>
                            Sedang Dibaiki
                        </option>
                        <option value="selesai" <?php echo $complaint['status'] === 'selesai' ? 'selected' : ''; ?>>
                            Selesai
                        </option>
                        <option value="dibatalkan" <?php echo $complaint['status'] === 'dibatalkan' ? 'selected' : ''; ?>>
                            Dibatalkan
                        </option>
                    </select>
                </div>

                <!-- Priority -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Keutamaan <span class="text-red-500">*</span>
                    </label>
                    <select name="priority" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="rendah" <?php echo $complaint['priority'] === 'rendah' ? 'selected' : ''; ?>>
                            Rendah
                        </option>
                        <option value="sederhana" <?php echo $complaint['priority'] === 'sederhana' ? 'selected' : ''; ?>>
                            Sederhana
                        </option>
                        <option value="tinggi" <?php echo $complaint['priority'] === 'tinggi' ? 'selected' : ''; ?>>
                            Tinggi
                        </option>
                        <option value="kritikal" <?php echo $complaint['priority'] === 'kritikal' ? 'selected' : ''; ?>>
                            Kritikal
                        </option>
                    </select>
                </div>

                <!-- Assign Officer -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Tugaskan Kepada Pegawai
                    </label>
                    <select name="officer_id"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">-- Belum Ditugaskan --</option>
                        <?php foreach ($officers as $officer): ?>
                        <option value="<?php echo $officer['id']; ?>"
                                <?php echo $complaint['officer_id'] == $officer['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($officer['nama']); ?>
                            <?php if (!empty($officer['email'])): ?>
                                (<?php echo htmlspecialchars($officer['email']); ?>)
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Progress -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Kemajuan: <span id="progress-value"><?php echo $complaint['progress']; ?></span>%
                    </label>
                    <input type="range" name="progress" id="progress-slider"
                           min="0" max="100" step="5"
                           value="<?php echo $complaint['progress']; ?>"
                           class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>0%</span>
                        <span>25%</span>
                        <span>50%</span>
                        <span>75%</span>
                        <span>100%</span>
                    </div>
                </div>

                <!-- Notes/Comments -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Catatan / Keterangan
                    </label>
                    <textarea name="keterangan" rows="4"
                              placeholder="Tambah catatan tentang perubahan status ini..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Catatan ini akan direkodkan dalam sejarah status</p>
                </div>

                <!-- Current Information Display -->
                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <h3 class="font-semibold text-gray-800 mb-2">Maklumat Semasa</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Pengadu:</span>
                            <span class="font-medium ml-2"><?php echo htmlspecialchars($complaint['nama_pengadu']); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Email:</span>
                            <span class="font-medium ml-2"><?php echo htmlspecialchars($complaint['email']); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Jenis:</span>
                            <span class="font-medium ml-2"><?php echo ucfirst($complaint['jenis']); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Tarikh Dibuat:</span>
                            <span class="font-medium ml-2"><?php echo date('d/m/Y', strtotime($complaint['created_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4 pt-4 border-t border-gray-200">
                    <button type="submit"
                            class="flex-1 px-6 py-3 gradient-bg text-white rounded-lg hover:opacity-90 transition font-semibold">
                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
                    </button>
                    <a href="view_complaint.php?id=<?php echo $complaint_id; ?>"
                       class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold">
                        <i class="fas fa-times mr-2"></i>Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Update progress value display
        const progressSlider = document.getElementById('progress-slider');
        const progressValue = document.getElementById('progress-value');

        progressSlider.addEventListener('input', function() {
            progressValue.textContent = this.value;
        });

        // Auto-set progress to 100% when status is 'selesai'
        const statusSelect = document.querySelector('select[name="status"]');
        statusSelect.addEventListener('change', function() {
            if (this.value === 'selesai') {
                progressSlider.value = 100;
                progressValue.textContent = '100';
            }
        });
    </script>
</body>
</html>
