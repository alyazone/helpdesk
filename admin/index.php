<?php
/**
 * Admin Dashboard
 * PLAN Malaysia Selangor - Helpdesk System
 */

require_once __DIR__ . '/../config/config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.html?redirect=admin');
}

$db = getDB();

// Get statistics
$stmt = $db->query("SELECT COUNT(*) as total FROM complaints");
$total_complaints = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE status = 'pending'");
$pending = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE status IN ('dalam_pemeriksaan', 'sedang_dibaiki')");
$in_progress = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE status = 'selesai'");
$completed = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$total_users = $stmt->fetch()['total'];

// Get recent complaints
$stmt = $db->query("
    SELECT c.*, o.nama as officer_name
    FROM complaints c
    LEFT JOIN officers o ON c.officer_id = o.id
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
    <title>Admin Dashboard - Sistem Helpdesk</title>
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
                    <i class="fas fa-shield-alt text-2xl"></i>
                    <span class="font-semibold text-lg">Admin Dashboard</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="complaints.php" class="hover:opacity-80">
                        <i class="fas fa-clipboard-list mr-2"></i>Aduan
                    </a>
                    <a href="users.php" class="hover:opacity-80">
                        <i class="fas fa-users mr-2"></i>Pengguna
                    </a>
                    <a href="officers.php" class="hover:opacity-80">
                        <i class="fas fa-user-tie mr-2"></i>Pegawai
                    </a>
                    <a href="reports.php" class="hover:opacity-80">
                        <i class="fas fa-chart-bar mr-2"></i>Laporan
                    </a>
                    <span class="text-sm"><?php echo htmlspecialchars($user['nama']); ?></span>
                    <a href="../api/logout.php" class="px-4 py-2 bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30 transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Log Keluar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
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

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Pending</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $pending; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-gray-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Dalam Proses</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $in_progress; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-cog text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Selesai</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $completed; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Pengguna</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $total_users; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Complaints -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Aduan Terkini</h2>
                <a href="complaints.php" class="px-4 py-2 gradient-bg text-white rounded-lg hover:opacity-90 transition">
                    Lihat Semua
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
                        <?php foreach ($recent_complaints as $complaint): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4 text-sm"><?php echo htmlspecialchars($complaint['ticket_number']); ?></td>
                            <td class="py-3 px-4 text-sm"><?php echo htmlspecialchars(substr($complaint['perkara'], 0, 50)); ?>...</td>
                            <td class="py-3 px-4 text-sm"><?php echo htmlspecialchars($complaint['nama_pengadu']); ?></td>
                            <td class="py-3 px-4">
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
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $color; ?>">
                                    <?php echo $label; ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm"><?php echo date('d/m/Y', strtotime($complaint['created_at'])); ?></td>
                            <td class="py-3 px-4">
                                <a href="view_complaint.php?id=<?php echo $complaint['id']; ?>" class="text-purple-600 hover:text-purple-800">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
