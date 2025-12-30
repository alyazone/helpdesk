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

// Top users by complaints submitted
$stmt = $db->prepare("
    SELECT u.nama_penuh as nama, COUNT(c.id) as total_complaints
    FROM users u
    LEFT JOIN complaints c ON u.id = c.user_id AND DATE(c.created_at) BETWEEN ? AND ?
    GROUP BY u.id, u.nama_penuh
    HAVING total_complaints > 0
    ORDER BY total_complaints DESC
    LIMIT 10
");
$stmt->execute([$date_from, $date_to]);
$top_users = $stmt->fetchAll();

// Completion rate trend (weekly)
$stmt = $db->prepare("
    SELECT
        YEARWEEK(created_at, 1) as week_key,
        DATE(DATE_SUB(created_at, INTERVAL WEEKDAY(created_at) DAY)) as week_start,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as completed
    FROM complaints
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY week_key, week_start
    ORDER BY week_start ASC
");
$stmt->execute([$date_from, $date_to]);
$completion_trend = $stmt->fetchAll();

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

// Feedback statistics (for completed complaints with feedback)
$stmt = $db->prepare("
    SELECT rating, COUNT(*) as count,
           AVG(CASE
               WHEN rating = 'cemerlang' THEN 5
               WHEN rating = 'baik' THEN 4
               WHEN rating = 'memuaskan' THEN 3
               WHEN rating = 'tidak_memuaskan' THEN 2
               ELSE 0
           END) as avg_score
    FROM complaints
    WHERE status = 'selesai'
    AND rating IS NOT NULL
    AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY rating
    ORDER BY count DESC
");
$stmt->execute([$date_from, $date_to]);
$feedback_stats = [];
foreach ($stmt->fetchAll() as $row) {
    $feedback_stats[$row['rating']] = $row['count'];
}

// Total completed complaints with feedback
$stmt = $db->prepare("
    SELECT COUNT(*) as total,
           AVG(CASE
               WHEN rating = 'cemerlang' THEN 5
               WHEN rating = 'baik' THEN 4
               WHEN rating = 'memuaskan' THEN 3
               WHEN rating = 'tidak_memuaskan' THEN 2
               ELSE 0
           END) as overall_avg
    FROM complaints
    WHERE status = 'selesai'
    AND rating IS NOT NULL
    AND DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$date_from, $date_to]);
$feedback_summary = $stmt->fetch();
$total_feedback = $feedback_summary['total'];
$overall_avg_score = round($feedback_summary['overall_avg'] ?? 0, 2);

// Feedback with comments
$stmt = $db->prepare("
    SELECT COUNT(*) as total
    FROM complaints
    WHERE status = 'selesai'
    AND rating IS NOT NULL
    AND feedback_comment IS NOT NULL
    AND feedback_comment != ''
    AND DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$date_from, $date_to]);
$feedback_with_comments = $stmt->fetch()['total'];

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
                        <i class="fas fa-file-csv mr-2"></i>Export Aduan CSV
                    </a>
                </div>
                <div class="mt-6">
                    <a href="export_feedback_csv.php?date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>"
                       class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 inline-block">
                        <i class="fas fa-star mr-2"></i>Export Maklum Balas CSV
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

        <!-- Feedback Statistics Summary -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Statistik Maklum Balas Pengguna</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-purple-700 mb-1">Jumlah Maklum Balas</p>
                            <p class="text-3xl font-bold text-purple-900"><?php echo $total_feedback; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-purple-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-comments text-purple-700 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-green-700 mb-1">Purata Skor</p>
                            <p class="text-3xl font-bold text-green-900"><?php echo $overall_avg_score; ?>/5</p>
                        </div>
                        <div class="w-12 h-12 bg-green-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-star text-green-700 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-blue-700 mb-1">Dengan Komen</p>
                            <p class="text-3xl font-bold text-blue-900"><?php echo $feedback_with_comments; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-blue-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-comment-dots text-blue-700 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feedback breakdown -->
            <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-yellow-700 mb-1">Cemerlang</p>
                            <p class="text-2xl font-bold text-yellow-900"><?php echo $feedback_stats['cemerlang'] ?? 0; ?></p>
                        </div>
                        <div class="text-yellow-600">
                            <i class="fas fa-star text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-green-700 mb-1">Baik</p>
                            <p class="text-2xl font-bold text-green-900"><?php echo $feedback_stats['baik'] ?? 0; ?></p>
                        </div>
                        <div class="text-green-600">
                            <i class="fas fa-thumbs-up text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-blue-700 mb-1">Memuaskan</p>
                            <p class="text-2xl font-bold text-blue-900"><?php echo $feedback_stats['memuaskan'] ?? 0; ?></p>
                        </div>
                        <div class="text-blue-600">
                            <i class="fas fa-smile text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-red-700 mb-1">Tidak Memuaskan</p>
                            <p class="text-2xl font-bold text-red-900"><?php echo $feedback_stats['tidak_memuaskan'] ?? 0; ?></p>
                        </div>
                        <div class="text-red-600">
                            <i class="fas fa-frown text-2xl"></i>
                        </div>
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

            <!-- Top Users Chart -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Pengguna Teraktif</h3>
                <canvas id="topUsersChart"></canvas>
            </div>

            <!-- Feedback Chart -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Taburan Maklum Balas</h3>
                <canvas id="feedbackChart"></canvas>
            </div>

            <!-- Completion Rate Trend -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Trend Kadar Penyiapan</h3>
                <canvas id="completionTrendChart"></canvas>
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

        // Top Users Chart
        new Chart(document.getElementById('topUsersChart'), {
            type: 'bar',
            data: {
                labels: [
                    <?php foreach ($top_users as $user): ?>
                        '<?php echo htmlspecialchars($user['nama']); ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: 'Jumlah Aduan',
                    data: [
                        <?php foreach ($top_users as $user): ?>
                            <?php echo $user['total_complaints']; ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: '#3B82F6',
                    borderColor: '#2563EB',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Jumlah Aduan: ' + context.parsed.x;
                            }
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

        // Feedback Chart
        new Chart(document.getElementById('feedbackChart'), {
            type: 'doughnut',
            data: {
                labels: ['Cemerlang', 'Baik', 'Memuaskan', 'Tidak Memuaskan'],
                datasets: [{
                    data: [
                        <?php echo $feedback_stats['cemerlang'] ?? 0; ?>,
                        <?php echo $feedback_stats['baik'] ?? 0; ?>,
                        <?php echo $feedback_stats['memuaskan'] ?? 0; ?>,
                        <?php echo $feedback_stats['tidak_memuaskan'] ?? 0; ?>
                    ],
                    backgroundColor: ['#F59E0B', '#10B981', '#3B82F6', '#EF4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                label += ' (' + percentage + '%)';
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // Completion Rate Trend Chart
        new Chart(document.getElementById('completionTrendChart'), {
            type: 'line',
            data: {
                labels: [
                    <?php foreach ($completion_trend as $week): ?>
                        '<?php echo date('d/m', strtotime($week['week_start'])); ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: 'Kadar Penyiapan (%)',
                    data: [
                        <?php foreach ($completion_trend as $week): ?>
                            <?php
                                $completion_rate = $week['total'] > 0
                                    ? round(($week['completed'] / $week['total']) * 100, 1)
                                    : 0;
                                echo $completion_rate;
                            ?>,
                        <?php endforeach; ?>
                    ],
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
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
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Kadar Penyiapan: ' + context.parsed.y + '%';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
