<?php
/**
 * Bahagian Pentadbiran & Kewangan - Dashboard
 * PLAN Malaysia Selangor - Helpdesk System
 */

require_once __DIR__ . '/../../config/config.php';

// Check if user is logged in and has Bahagian Pentadbiran & Kewangan role
if (!isLoggedIn() || !isBahagianPentadbiranKewangan()) {
    redirect('../../login.html');
}

$db = getDB();

// Get statistics
// Total complaints received (dimajukan_pegawai_pelulus status and onwards)
$stmt = $db->query("
    SELECT COUNT(*) as total FROM complaints
    WHERE workflow_status IN ('dimajukan_pegawai_pelulus', 'diluluskan', 'ditolak', 'selesai')
");
$total_received = $stmt->fetch()['total'];

// Need approval (newly received)
$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE workflow_status = 'dimajukan_pegawai_pelulus'");
$perlu_kelulusan = $stmt->fetch()['total'];

// Approved
$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE workflow_status = 'diluluskan'");
$diluluskan = $stmt->fetch()['total'];

// Rejected
$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE workflow_status = 'ditolak'");
$ditolak = $stmt->fetch()['total'];

// Completed
$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE workflow_status = 'selesai'");
$selesai = $stmt->fetch()['total'];

// Get recent complaints
$stmt = $db->query("
    SELECT c.*,
           u_unit_aset.nama_penuh as unit_aset_officer_name,
           bka.anggaran_kos_penyelenggaraan,
           bka.keputusan_status
    FROM complaints c
    LEFT JOIN users u_unit_aset ON c.unit_aset_processed_by = u_unit_aset.id
    LEFT JOIN borang_kerosakan_aset bka ON c.id = bka.complaint_id
    WHERE c.workflow_status IN ('dimajukan_pegawai_pelulus', 'diluluskan', 'ditolak', 'selesai')
    ORDER BY c.updated_at DESC
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
    <title>Bahagian Pentadbiran & Kewangan - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="gradient-bg text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-dollar-sign text-2xl"></i>
                    <span class="font-semibold text-lg">Bahagian Pentadbiran & Kewangan</span>
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
            <h1 class="text-3xl font-bold text-gray-800">Dashboard Bahagian Pentadbiran & Kewangan</h1>
            <p class="text-gray-600 mt-2">Selamat datang, <?php echo htmlspecialchars($user['nama']); ?></p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Jumlah Diterima</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $total_received; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-inbox text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Perlu Kelulusan</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $perlu_kelulusan; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-circle text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Diluluskan</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $diluluskan; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Ditolak</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $ditolak; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
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
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Anggaran Kos (RM)</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Tarikh</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_complaints)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2"></i>
                                <p>Tiada aduan</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($recent_complaints as $complaint): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4 text-sm font-semibold text-orange-600">
                                <?php echo htmlspecialchars($complaint['ticket_number']); ?>
                            </td>
                            <td class="py-3 px-4 text-sm">
                                <?php echo htmlspecialchars(substr($complaint['perkara'], 0, 50)); ?>...
                            </td>
                            <td class="py-3 px-4 text-sm">
                                <?php echo htmlspecialchars($complaint['nama_pengadu']); ?>
                            </td>
                            <td class="py-3 px-4 text-sm font-semibold">
                                <?php
                                if (!empty($complaint['anggaran_kos_penyelenggaraan'])) {
                                    echo number_format($complaint['anggaran_kos_penyelenggaraan'], 2);
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td class="py-3 px-4">
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
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $color; ?>">
                                    <?php echo $label; ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm"><?php echo date('d/m/Y', strtotime($complaint['updated_at'])); ?></td>
                            <td class="py-3 px-4">
                                <a href="view_complaint.php?id=<?php echo $complaint['id']; ?>"
                                   class="text-orange-600 hover:text-orange-800" title="Lihat & Luluskan">
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

    <!-- Role Switcher Component -->
    <script src="../../assets/js/role-switcher.js"></script>
</body>
</html>
