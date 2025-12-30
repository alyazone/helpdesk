<?php
/**
 * Admin - Completed Complaints Report
 */

require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.html?redirect=admin');
}

$db = getDB();

// Get filter parameters
$selected_month = $_GET['bulan'] ?? date('m');
$selected_year = $_GET['tahun'] ?? date('Y');

// Build query for completed complaints
$stmt = $db->prepare("
    SELECT c.*, o.nama as officer_name
    FROM complaints c
    LEFT JOIN officers o ON c.officer_id = o.id
    WHERE c.status = 'selesai'
    AND MONTH(c.completed_at) = ?
    AND YEAR(c.completed_at) = ?
    ORDER BY c.completed_at DESC
");
$stmt->execute([$selected_month, $selected_year]);
$completed_complaints = $stmt->fetchAll();

// Generate year options (from 2020 to current year + 1)
$years = range(2020, date('Y') + 1);

$user = getUser();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aduan Diselesaikan - Admin</title>
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
                    <a href="index.php" class="flex items-center space-x-2 hover:opacity-80">
                        <i class="fas fa-arrow-left"></i>
                        <span>Dashboard</span>
                    </a>
                    <span class="font-semibold text-lg">Aduan Diselesaikan</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="complaints.php" class="hover:opacity-80">
                        <i class="fas fa-clipboard-list mr-2"></i>Semua Aduan
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Laporan Aduan Diselesaikan</h1>
            <p class="text-gray-600">Senarai aduan yang telah diselesaikan</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6 no-print">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih bulan</label>
                    <select name="bulan" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <?php
                        $months = [
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Mac', '04' => 'April',
                            '05' => 'Mei', '06' => 'Jun', '07' => 'Julai', '08' => 'Ogos',
                            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Disember'
                        ];
                        foreach ($months as $num => $name):
                        ?>
                            <option value="<?php echo $num; ?>" <?php echo $selected_month == $num ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih tahun</label>
                    <select name="tahun" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <?php foreach ($years as $year): ?>
                            <option value="<?php echo $year; ?>" <?php echo $selected_year == $year ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="px-6 py-2 gradient-bg text-white rounded-lg hover:opacity-90 transition">
                    <i class="fas fa-search mr-2"></i>Papar
                </button>

                <button type="button" onclick="window.print()" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-print mr-2"></i>Cetak
                </button>
            </form>
        </div>

        <!-- Summary Card -->
        <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-green-700 mb-1">Jumlah Aduan Diselesaikan</p>
                    <p class="text-4xl font-bold text-green-900"><?php echo count($completed_complaints); ?></p>
                    <p class="text-sm text-gray-600 mt-1">
                        <?php echo $months[$selected_month]; ?> <?php echo $selected_year; ?>
                    </p>
                </div>
                <div class="w-20 h-20 bg-green-200 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-double text-green-700 text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Complaints Table -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Senarai Aduan/Cadangan</h2>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">No.</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Jenis</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Perkara</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Pegawai Terima</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Tarikh Dihantar</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Tarikh Selesai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($completed_complaints)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2"></i>
                                <p>Tiada aduan diselesaikan bagi bulan ini</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($completed_complaints as $index => $complaint): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <!-- No. -->
                                <td class="py-3 px-4 text-sm font-medium text-gray-700">
                                    <?php echo $index + 1; ?>
                                </td>

                                <!-- Jenis -->
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded text-xs font-semibold <?php echo $complaint['jenis'] === 'aduan' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'; ?>">
                                        <?php echo ucfirst($complaint['jenis']); ?>
                                    </span>
                                </td>

                                <!-- Perkara -->
                                <td class="py-3 px-4">
                                    <a href="view_complaint.php?id=<?php echo $complaint['id']; ?>" class="text-blue-600 hover:text-blue-800 hover:underline">
                                        <?php echo htmlspecialchars($complaint['perkara']); ?>
                                    </a>
                                </td>

                                <!-- Pegawai Terima -->
                                <td class="py-3 px-4 text-sm text-gray-700">
                                    <?php echo htmlspecialchars($complaint['officer_name'] ?? '-'); ?>
                                </td>

                                <!-- Tarikh Dihantar -->
                                <td class="py-3 px-4 text-sm text-gray-700">
                                    <?php echo date('d-m-Y', strtotime($complaint['created_at'])); ?>
                                </td>

                                <!-- Tarikh Selesai -->
                                <td class="py-3 px-4 text-sm text-gray-700">
                                    <?php echo date('d-m-Y', strtotime($complaint['completed_at'])); ?>
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
