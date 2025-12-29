<?php
/**
 * Unit Aduan Dalaman - Dashboard
 * PLAN Malaysia Selangor - Helpdesk System
 */

require_once __DIR__ . '/../../config/config.php';

// Check if user is logged in and has Unit Aduan Dalaman role
if (!isLoggedIn() || !isUnitAduanDalaman()) {
    redirect('../../login.html');
}

$db = getDB();

// Get statistics
$stmt = $db->query("SELECT COUNT(*) as total FROM complaints");
$total_complaints = $stmt->fetch()['total'];

// Complaints that need verification (baru status)
$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE workflow_status = 'baru'");
$perlu_disahkan = $stmt->fetch()['total'];

// Complaints that have been verified
$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE workflow_status IN ('disahkan_unit_aduan', 'dimajukan_unit_aset', 'dalam_semakan_unit_aset', 'dimajukan_pegawai_pelulus', 'diluluskan', 'selesai')");
$sudah_disahkan = $stmt->fetch()['total'];

// Complaints forwarded to Unit Aset
$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE workflow_status IN ('dimajukan_unit_aset', 'dalam_semakan_unit_aset', 'dimajukan_pegawai_pelulus', 'diluluskan')");
$dimajukan_unit_aset = $stmt->fetch()['total'];

// Completed complaints
$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE workflow_status = 'selesai'");
$selesai = $stmt->fetch()['total'];

// Get recent complaints
$stmt = $db->query("
    SELECT c.*,
           u.nama_penuh as verified_by_name,
           uao.nama as dimajukan_ke_nama
    FROM complaints c
    LEFT JOIN users u ON c.unit_aduan_verified_by = u.id
    LEFT JOIN unit_aset_officers uao ON c.dimajukan_ke = uao.id
    ORDER BY c.created_at DESC
    LIMIT 10
");
$recent_complaints = $stmt->fetchAll();

$user = getUser();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unit Aduan Dalaman - Dashboard</title>
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
                    <i class="fas fa-clipboard-check text-2xl"></i>
                    <span class="font-semibold text-lg">Unit Aduan Dalaman</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="complaints.php" class="hover:opacity-80">
                        <i class="fas fa-clipboard-list mr-2"></i>Senarai Aduan
                    </a>
                    <span class="text-sm"><?php echo htmlspecialchars($user['nama']); ?></span>
                    <a href="../../api/logout.php" class="px-4 py-2 bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30 transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Log Keluar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Dashboard Unit Aduan Dalaman</h1>
            <p class="text-gray-600 mt-2">Selamat datang, <?php echo htmlspecialchars($user['nama']); ?></p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Jumlah Aduan</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $total_complaints; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Perlu Disahkan</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $perlu_disahkan; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-circle text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Sudah Disahkan</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $sudah_disahkan; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Dimajukan ke Unit Aset</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $dimajukan_unit_aset; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-paper-plane text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-gray-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Selesai</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $selesai; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-double text-gray-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Complaints -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Aduan Terkini</h2>
                <a href="complaints.php" class="px-4 py-2 gradient-bg text-white rounded-lg hover:opacity-90 transition">
                    <i class="fas fa-list mr-2"></i>Lihat Semua
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">No. Tiket</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Perkara</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Pengadu</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status Workflow</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Tarikh</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_complaints)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2"></i>
                                <p>Tiada aduan</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($recent_complaints as $complaint): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4 text-sm font-semibold text-purple-600">
                                <?php echo htmlspecialchars($complaint['ticket_number']); ?>
                            </td>
                            <td class="py-3 px-4 text-sm">
                                <?php echo htmlspecialchars(substr($complaint['perkara'], 0, 50)); ?>...
                            </td>
                            <td class="py-3 px-4 text-sm">
                                <?php echo htmlspecialchars($complaint['nama_pengadu']); ?>
                            </td>
                            <td class="py-3 px-4">
                                <?php
                                $workflow_colors = [
                                    'baru' => 'bg-yellow-100 text-yellow-800',
                                    'disahkan_unit_aduan' => 'bg-green-100 text-green-800',
                                    'dimajukan_unit_aset' => 'bg-purple-100 text-purple-800',
                                    'dalam_semakan_unit_aset' => 'bg-blue-100 text-blue-800',
                                    'dimajukan_pegawai_pelulus' => 'bg-indigo-100 text-indigo-800',
                                    'diluluskan' => 'bg-teal-100 text-teal-800',
                                    'ditolak' => 'bg-red-100 text-red-800',
                                    'selesai' => 'bg-gray-100 text-gray-800'
                                ];
                                $workflow_labels = [
                                    'baru' => 'Baru',
                                    'disahkan_unit_aduan' => 'Disahkan Unit Aduan',
                                    'dimajukan_unit_aset' => 'Dimajukan ke Unit Aset',
                                    'dalam_semakan_unit_aset' => 'Dalam Semakan Unit Aset',
                                    'dimajukan_pegawai_pelulus' => 'Dimajukan ke Pegawai Pelulus',
                                    'diluluskan' => 'Diluluskan',
                                    'ditolak' => 'Ditolak',
                                    'selesai' => 'Selesai'
                                ];
                                $color = $workflow_colors[$complaint['workflow_status']] ?? 'bg-gray-100 text-gray-800';
                                $label = $workflow_labels[$complaint['workflow_status']] ?? $complaint['workflow_status'];
                                ?>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $color; ?>">
                                    <?php echo $label; ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm"><?php echo date('d/m/Y', strtotime($complaint['created_at'])); ?></td>
                            <td class="py-3 px-4">
                                <a href="view_complaint.php?id=<?php echo $complaint['id']; ?>"
                                   class="text-purple-600 hover:text-purple-800 mr-2" title="Lihat & Proses">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
