<?php
/**
 * Admin - Reports and Analytics
 */

require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.html?redirect=admin');
}

$db = getDB();

// Get date range from query parameters
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$date_to = $_GET['date_to'] ?? date('Y-m-d'); // Today

// Get complaints within date range
$stmt = $db->prepare("
    SELECT c.*, o.nama as officer_name
    FROM complaints c
    LEFT JOIN officers o ON c.officer_id = o.id
    WHERE DATE(c.created_at) BETWEEN ? AND ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$date_from, $date_to]);
$complaints = $stmt->fetchAll();

// Statistics by status
$stmt = $db->prepare("
    SELECT status, COUNT(*) as count
    FROM complaints
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY status
");
$stmt->execute([$date_from, $date_to]);
$status_stats = [];
foreach ($stmt->fetchAll() as $row) {
    $status_stats[$row['status']] = $row['count'];
}

// Statistics by jenis (type)
$stmt = $db->prepare("
    SELECT jenis, COUNT(*) as count
    FROM complaints
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY jenis
");
$stmt->execute([$date_from, $date_to]);
$jenis_stats = [];
foreach ($stmt->fetchAll() as $row) {
    $jenis_stats[$row['jenis']] = $row['count'];
}

// Statistics by priority
$stmt = $db->prepare("
    SELECT priority, COUNT(*) as count
    FROM complaints
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY priority
");
$stmt->execute([$date_from, $date_to]);
$priority_stats = [];
foreach ($stmt->fetchAll() as $row) {
    $priority_stats[$row['priority']] = $row['count'];
}

// Top officers by complaints handled
$stmt = $db->prepare("
    SELECT o.nama, COUNT(c.id) as total_complaints,
           SUM(CASE WHEN c.status = 'selesai' THEN 1 ELSE 0 END) as completed_complaints
    FROM officers o
    LEFT JOIN complaints c ON o.id = c.officer_id AND DATE(c.created_at) BETWEEN ? AND ?
    GROUP BY o.id, o.nama
    HAVING total_complaints > 0
    ORDER BY total_complaints DESC
    LIMIT 10
");
$stmt->execute([$date_from, $date_to]);
$top_officers = $stmt->fetchAll();

// Average resolution time (for completed complaints)
$stmt = $db->prepare("
    SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_hours
    FROM complaints
    WHERE status = 'selesai'
    AND completed_at IS NOT NULL
    AND DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$date_from, $date_to]);
$avg_resolution = $stmt->fetch();
$avg_hours = round($avg_resolution['avg_hours'] ?? 0, 1);

// Daily complaints trend
$stmt = $db->prepare("
    SELECT DATE(created_at) as date, COUNT(*) as count
    FROM complaints
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute([$date_from, $date_to]);
$daily_trend = $stmt->fetchAll();

$user = getUser();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Analisis - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <a href="index.php" class="flex items-center space-x-2 hover:opacity-80">
                        <i class="fas fa-arrow-left"></i>
                        <span>Dashboard</span>
                    </a>
                    <span class="font-semibold text-lg">Laporan & Analisis</span>
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
                    <span class="text-sm"><?php echo htmlspecialchars($user['nama']); ?></span>
                    <a href="../api/logout.php" class="px-4 py-2 bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30 transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Log Keluar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Date Range Filter -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6 no-print">
            <form method="GET" class="flex flex-wrap gap-4 items-center">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tarikh Mula</label>
                    <input type="date" name="date_from" value="<?php echo $date_from; ?>"
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tarikh Akhir</label>
                    <input type="date" name="date_to" value="<?php echo $date_to; ?>"
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="mt-6">
                    <button type="submit" class="px-6 py-2 gradient-bg text-white rounded-lg hover:opacity-90">
                        <i class="fas fa-search mr-2"></i>Jana Laporan
                    </button>
                </div>
                <div class="mt-6">
                    <button type="button" onclick="window.print()" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <i class="fas fa-print mr-2"></i>Cetak
                    </button>
                </div>
                <div class="mt-6">
                    <a href="export_csv.php?date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>"
                       class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 inline-block">
                        <i class="fas fa-file-csv mr-2"></i>Export CSV
                    </a>
                </div>
            </form>
        </div>

        <!-- Summary Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Jumlah Aduan</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo count($complaints); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Selesai</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $status_stats['selesai'] ?? 0; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Dalam Proses</p>
                        <p class="text-3xl font-bold text-gray-800">
                            <?php echo ($status_stats['dalam_pemeriksaan'] ?? 0) + ($status_stats['sedang_dibaiki'] ?? 0); ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-cog text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Purata Masa Selesai</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $avg_hours; ?>h</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Status Chart -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Statistik Status</h3>
                <canvas id="statusChart"></canvas>
            </div>

            <!-- Jenis Chart -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Jenis Aduan</h3>
                <canvas id="jenisChart"></canvas>
            </div>

            <!-- Priority Chart -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Keutamaan</h3>
                <canvas id="priorityChart"></canvas>
            </div>

            <!-- Top Officers -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Top Pegawai</h3>
                <div class="space-y-3">
                    <?php foreach ($top_officers as $officer): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                                <?php echo strtoupper(substr($officer['nama'], 0, 1)); ?>
                            </div>
                            <div>
                                <p class="font-medium"><?php echo htmlspecialchars($officer['nama']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo $officer['completed_complaints']; ?> selesai / <?php echo $officer['total_complaints']; ?> jumlah</p>
                            </div>
                        </div>
                        <span class="font-bold text-purple-600"><?php echo $officer['total_complaints']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Daily Trend Chart -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Trend Harian</h3>
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    <script>
        // Status Chart
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Dalam Pemeriksaan', 'Sedang Dibaiki', 'Selesai', 'Dibatalkan'],
                datasets: [{
                    data: [
                        <?php echo $status_stats['pending'] ?? 0; ?>,
                        <?php echo $status_stats['dalam_pemeriksaan'] ?? 0; ?>,
                        <?php echo $status_stats['sedang_dibaiki'] ?? 0; ?>,
                        <?php echo $status_stats['selesai'] ?? 0; ?>,
                        <?php echo $status_stats['dibatalkan'] ?? 0; ?>
                    ],
                    backgroundColor: ['#9CA3AF', '#3B82F6', '#F59E0B', '#10B981', '#EF4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });

        // Jenis Chart
        new Chart(document.getElementById('jenisChart'), {
            type: 'pie',
            data: {
                labels: ['Aduan', 'Cadangan'],
                datasets: [{
                    data: [
                        <?php echo $jenis_stats['aduan'] ?? 0; ?>,
                        <?php echo $jenis_stats['cadangan'] ?? 0; ?>
                    ],
                    backgroundColor: ['#EF4444', '#3B82F6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });

        // Priority Chart
        new Chart(document.getElementById('priorityChart'), {
            type: 'bar',
            data: {
                labels: ['Rendah', 'Sederhana', 'Tinggi', 'Kritikal'],
                datasets: [{
                    label: 'Jumlah',
                    data: [
                        <?php echo $priority_stats['rendah'] ?? 0; ?>,
                        <?php echo $priority_stats['sederhana'] ?? 0; ?>,
                        <?php echo $priority_stats['tinggi'] ?? 0; ?>,
                        <?php echo $priority_stats['kritikal'] ?? 0; ?>
                    ],
                    backgroundColor: ['#9CA3AF', '#3B82F6', '#F59E0B', '#EF4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Trend Chart
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: [
                    <?php foreach ($daily_trend as $day): ?>
                        '<?php echo date('d/m', strtotime($day['date'])); ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: 'Aduan',
                    data: [
                        <?php foreach ($daily_trend as $day): ?>
                            <?php echo $day['count']; ?>,
                        <?php endforeach; ?>
                    ],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
