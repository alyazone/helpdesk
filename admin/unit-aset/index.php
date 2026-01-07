<?php
/**
 * Unit Aset - Dashboard
 * PLAN Malaysia Selangor - Helpdesk System
 */

require_once __DIR__ . '/../../config/config.php';

// Check if user is logged in and has Unit Aset role
if (!isLoggedIn() || !isUnitAset()) {
    redirect('../../login.html');
}

$db = getDB();

// Get statistics
// Total complaints received (dimajukan_unit_aset status and onwards)
$stmt = $db->query("
    SELECT COUNT(*) as total FROM complaints
    WHERE workflow_status IN ('dimajukan_unit_aset', 'dalam_semakan_unit_aset', 'dimajukan_pegawai_pelulus', 'diluluskan', 'selesai')
");
$total_received = $stmt->fetch()['total'];

// Need to review (newly received)
$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE workflow_status = 'dimajukan_unit_aset'");
$perlu_semak = $stmt->fetch()['total'];

// Under review by Unit Aset
$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE workflow_status = 'dalam_semakan_unit_aset'");
$dalam_semakan = $stmt->fetch()['total'];

// Forwarded to approval officer
$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE workflow_status = 'dimajukan_pegawai_pelulus'");
$dimajukan_pelulus = $stmt->fetch()['total'];

// Approved
$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE workflow_status = 'diluluskan'");
$diluluskan = $stmt->fetch()['total'];

// Get recent complaints
$stmt = $db->query("
    SELECT c.*,
           uao.nama as dimajukan_ke_nama,
           u_verified.nama_penuh as unit_aduan_officer
    FROM complaints c
    LEFT JOIN unit_aset_officers uao ON c.dimajukan_ke = uao.id
    LEFT JOIN users u_verified ON c.unit_aduan_verified_by = u_verified.id
    WHERE c.workflow_status IN ('dimajukan_unit_aset', 'dalam_semakan_unit_aset', 'dimajukan_pegawai_pelulus', 'diluluskan', 'selesai')
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
    <title>Unit Aset - Dashboard</title>
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
                    <i class="fas fa-box text-2xl"></i>
                    <span class="font-semibold text-lg">Unit Aset</span>
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
            <h1 class="text-3xl font-bold text-gray-800">Dashboard Unit Aset</h1>
            <p class="text-gray-600 mt-2">Selamat datang, <?php echo htmlspecialchars($user['nama']); ?></p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Jumlah Diterima</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $total_received; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-inbox text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Perlu Semakan</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $perlu_semak; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Dalam Semakan</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $dalam_semakan; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-search text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-indigo-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Dimajukan ke Pelulus</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $dimajukan_pelulus; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-paper-plane text-indigo-600 text-xl"></i>
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
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
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
                            <td class="py-3 px-4 text-sm font-semibold text-blue-600">
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
                                    'dimajukan_unit_aset' => 'bg-yellow-100 text-yellow-800',
                                    'dalam_semakan_unit_aset' => 'bg-purple-100 text-purple-800',
                                    'dimajukan_pegawai_pelulus' => 'bg-indigo-100 text-indigo-800',
                                    'diluluskan' => 'bg-green-100 text-green-800',
                                    'selesai' => 'bg-gray-100 text-gray-800'
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
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $color; ?>">
                                    <?php echo $label; ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm"><?php echo date('d/m/Y', strtotime($complaint['updated_at'])); ?></td>
                            <td class="py-3 px-4">
                                <a href="view_complaint.php?id=<?php echo $complaint['id']; ?>"
                                   class="text-blue-600 hover:text-blue-800" title="Lihat & Proses">
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
