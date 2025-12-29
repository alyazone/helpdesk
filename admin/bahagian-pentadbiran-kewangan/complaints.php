<?php
/**
 * Bahagian Pentadbiran & Kewangan - Complaint List
 * PLAN Malaysia Selangor - Helpdesk System
 */

require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !isBahagianPentadbiranKewangan()) {
    redirect('../../login.html');
}

$db = getDB();

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$query = "
    SELECT c.*,
           u_unit_aset.nama_penuh as unit_aset_officer_name,
           bka.anggaran_kos_penyelenggaraan,
           bka.keputusan_status,
           bka.keputusan_tarikh
    FROM complaints c
    LEFT JOIN users u_unit_aset ON c.unit_aset_processed_by = u_unit_aset.id
    LEFT JOIN borang_kerosakan_aset bka ON c.id = bka.complaint_id
    WHERE c.workflow_status IN ('dimajukan_pegawai_pelulus', 'diluluskan', 'ditolak', 'selesai')
";

$params = [];

// Apply status filter
if ($status_filter !== 'all') {
    $query .= " AND c.workflow_status = ?";
    $params[] = $status_filter;
}

// Apply search filter
if (!empty($search)) {
    $query .= " AND (c.ticket_number LIKE ? OR c.perkara LIKE ? OR c.nama_pengadu LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$query .= " ORDER BY c.updated_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$complaints = $stmt->fetchAll();

$user = getUser();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senarai Aduan - Bahagian Pentadbiran & Kewangan</title>
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
                    <a href="index.php" class="flex items-center space-x-2 hover:opacity-80">
                        <i class="fas fa-arrow-left"></i>
                        <span>Dashboard</span>
                    </a>
                    <span class="font-semibold text-lg">Senarai Aduan</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm"><?php echo htmlspecialchars($user['nama']); ?></span>
                    <a href="../../api/logout.php" class="px-4 py-2 bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30 transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Log Keluar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Senarai Aduan</h1>
            <p class="text-gray-600 mt-2">Semak dan luluskan aduan yang dimajukan dari Unit Aset</p>
        </div>

        <!-- Filter and Search -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Cari tiket, perkara, atau nama pengadu..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                </div>

                <!-- Status Filter -->
                <div class="w-full md:w-64">
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                        <option value="dimajukan_pegawai_pelulus" <?php echo $status_filter === 'dimajukan_pegawai_pelulus' ? 'selected' : ''; ?>>Perlu Kelulusan</option>
                        <option value="diluluskan" <?php echo $status_filter === 'diluluskan' ? 'selected' : ''; ?>>Diluluskan</option>
                        <option value="ditolak" <?php echo $status_filter === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                        <option value="selesai" <?php echo $status_filter === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button type="submit" class="px-6 py-2 gradient-bg text-white rounded-lg hover:opacity-90 transition">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                    <a href="complaints.php" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                        <i class="fas fa-redo mr-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Complaints Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="gradient-bg text-white">
                        <tr>
                            <th class="text-left py-4 px-4 font-semibold">No. Tiket</th>
                            <th class="text-left py-4 px-4 font-semibold">Perkara</th>
                            <th class="text-left py-4 px-4 font-semibold">Pengadu</th>
                            <th class="text-left py-4 px-4 font-semibold">Jenis Aset</th>
                            <th class="text-left py-4 px-4 font-semibold">Anggaran Kos (RM)</th>
                            <th class="text-left py-4 px-4 font-semibold">Status</th>
                            <th class="text-left py-4 px-4 font-semibold">Tarikh</th>
                            <th class="text-left py-4 px-4 font-semibold">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($complaints)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-12 text-gray-500">
                                <i class="fas fa-inbox text-5xl mb-3 text-gray-400"></i>
                                <p class="text-lg">Tiada aduan dijumpai</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($complaints as $complaint): ?>
                        <tr class="border-b border-gray-100 hover:bg-orange-50 transition">
                            <td class="py-4 px-4">
                                <span class="font-semibold text-orange-600"><?php echo htmlspecialchars($complaint['ticket_number']); ?></span>
                            </td>
                            <td class="py-4 px-4">
                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars(substr($complaint['perkara'], 0, 60)); ?><?php echo strlen($complaint['perkara']) > 60 ? '...' : ''; ?></p>
                            </td>
                            <td class="py-4 px-4 text-sm text-gray-700">
                                <?php echo htmlspecialchars($complaint['nama_pengadu']); ?>
                            </td>
                            <td class="py-4 px-4 text-sm text-gray-700">
                                <?php echo htmlspecialchars($complaint['jenis_aset'] ?? '-'); ?>
                            </td>
                            <td class="py-4 px-4">
                                <?php
                                if (!empty($complaint['anggaran_kos_penyelenggaraan'])) {
                                    echo '<span class="font-semibold text-gray-800">' . number_format($complaint['anggaran_kos_penyelenggaraan'], 2) . '</span>';
                                } else {
                                    echo '<span class="text-gray-400">-</span>';
                                }
                                ?>
                            </td>
                            <td class="py-4 px-4">
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
                            <td class="py-4 px-4 text-sm text-gray-600">
                                <?php echo date('d/m/Y', strtotime($complaint['updated_at'])); ?>
                            </td>
                            <td class="py-4 px-4">
                                <a href="view_complaint.php?id=<?php echo $complaint['id']; ?>"
                                   class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition text-sm">
                                    <i class="fas fa-eye mr-2"></i>Lihat
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($complaints)): ?>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <p class="text-sm text-gray-600">
                    Jumlah: <span class="font-semibold"><?php echo count($complaints); ?></span> aduan
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
