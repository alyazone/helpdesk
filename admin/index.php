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

// Workflow status statistics
$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE workflow_status = 'baru'");
$stat_baru = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE workflow_status = 'disahkan_unit_aduan'");
$stat_unit_aduan = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE workflow_status IN ('dimajukan_unit_aset', 'dalam_semakan_unit_aset')");
$stat_unit_aset = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE workflow_status IN ('dimajukan_pegawai_pelulus', 'diluluskan') AND status != 'selesai'");
$stat_diluluskan = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE status = 'selesai'");
$stat_selesai = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM complaints WHERE status = 'dibatalkan'");
$stat_dibatalkan = $stmt->fetch()['total'];

// Complaint types breakdown for chart
$stmt = $db->query("
    SELECT perihal_kerosakan, COUNT(*) as count
    FROM complaints
    WHERE perihal_kerosakan IS NOT NULL AND perihal_kerosakan != ''
    GROUP BY perihal_kerosakan
    ORDER BY count DESC
    LIMIT 15
");
$complaint_categories = $stmt->fetchAll();

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <!-- Baru -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center mb-3">
                        <i class="fas fa-exclamation text-pink-600 text-2xl"></i>
                    </div>
                    <p class="text-4xl font-bold text-gray-800 mb-2"><?php echo $stat_baru; ?></p>
                    <p class="text-sm text-gray-600 font-medium">Baru</p>
                </div>
            </div>

            <!-- Disahkan Unit Aduan -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-3">
                        <i class="fas fa-thumbs-up text-blue-600 text-2xl"></i>
                    </div>
                    <p class="text-4xl font-bold text-gray-800 mb-2"><?php echo $stat_unit_aduan; ?></p>
                    <p class="text-sm text-gray-600 font-medium">Disahkan Unit Aduan</p>
                </div>
            </div>

            <!-- Disahkan Unit Aset -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mb-3">
                        <i class="fas fa-check text-yellow-600 text-2xl"></i>
                    </div>
                    <p class="text-4xl font-bold text-gray-800 mb-2"><?php echo $stat_unit_aset; ?></p>
                    <p class="text-sm text-gray-600 font-medium">Disahkan Unit Aset</p>
                </div>
            </div>

            <!-- Diluluskan/Dalam Proses -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-cyan-100 rounded-full flex items-center justify-center mb-3">
                        <i class="fas fa-cog text-cyan-600 text-2xl"></i>
                    </div>
                    <p class="text-4xl font-bold text-gray-800 mb-2"><?php echo $stat_diluluskan; ?></p>
                    <p class="text-sm text-gray-600 font-medium">Diluluskan / Dalam Proses</p>
                </div>
            </div>

            <!-- Diselesaikan -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-3">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                    <p class="text-4xl font-bold text-gray-800 mb-2"><?php echo $stat_selesai; ?></p>
                    <p class="text-sm text-gray-600 font-medium">Diselesaikan</p>
                </div>
            </div>

            <!-- Dibatalkan -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-3">
                        <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                    </div>
                    <p class="text-4xl font-bold text-gray-800 mb-2"><?php echo $stat_dibatalkan; ?></p>
                    <p class="text-sm text-gray-600 font-medium">Dibatalkan</p>
                </div>
            </div>
        </div>

        <!-- Statistics Chart and Recent Complaints -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Complaint Categories Chart -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Statistik Aduan/Cadangan</h2>
                <div class="flex items-center justify-center">
                    <div style="max-width: 400px; width: 100%;">
                        <canvas id="complaintChart"></canvas>
                    </div>
                    <div class="ml-8">
                        <div class="text-center">
                            <p class="text-sm text-gray-600 mb-1">Jumlah Aduan</p>
                            <p class="text-5xl font-bold text-purple-600"><?php echo $total_complaints; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Ringkasan Statistik</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-file-alt text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Jumlah Aduan</p>
                                <p class="text-2xl font-bold text-gray-800"><?php echo $total_complaints; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-check-double text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Diselesaikan</p>
                                <p class="text-2xl font-bold text-gray-800"><?php echo $stat_selesai; ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-green-700 font-semibold">
                                <?php echo $total_complaints > 0 ? round(($stat_selesai / $total_complaints) * 100, 1) : 0; ?>%
                            </p>
                            <p class="text-xs text-gray-500">Kadar Penyelesaian</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-orange-50 to-orange-100 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-orange-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-hourglass-half text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Dalam Tindakan</p>
                                <p class="text-2xl font-bold text-gray-800"><?php echo $stat_baru + $stat_unit_aduan + $stat_unit_aset + $stat_diluluskan; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-red-50 to-red-100 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-ban text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Dibatalkan</p>
                                <p class="text-2xl font-bold text-gray-800"><?php echo $stat_dibatalkan; ?></p>
                            </div>
                        </div>
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

    <script>
        // Complaint Categories Chart
        const ctx = document.getElementById('complaintChart');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php foreach ($complaint_categories as $cat): ?>
                        '<?php echo addslashes(ucwords(str_replace('_', ' ', $cat['perihal_kerosakan']))); ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    data: [
                        <?php foreach ($complaint_categories as $cat): ?>
                            <?php echo $cat['count']; ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: [
                        '#8B5CF6', '#EC4899', '#10B981', '#F59E0B', '#3B82F6',
                        '#EF4444', '#14B8A6', '#F97316', '#6366F1', '#84CC16',
                        '#06B6D4', '#A855F7', '#22C55E', '#F43F5E', '#0EA5E9'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 11
                            },
                            padding: 10
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
