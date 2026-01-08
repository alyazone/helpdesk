<?php
/**
 * Unit IT / Sokongan - Complaint List
 * PLAN Malaysia Selangor - Helpdesk System
 */

require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !isUnitITSokongan()) {
    redirect('../../login.html');
}

$db = getDB();

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$query = "
    SELECT c.*,
           bka.anggaran_kos_penyelenggaraan,
           bka.keputusan_nama as approved_by,
           bka.keputusan_tarikh as approved_date,
           uito.nama as assigned_officer_name,
           u_pelulus.nama_penuh as pelulus_name,
           u_completed.nama_penuh as completed_by_name
    FROM complaints c
    LEFT JOIN borang_kerosakan_aset bka ON c.id = bka.complaint_id
    LEFT JOIN unit_it_sokongan_officers uito ON c.unit_it_officer_id = uito.id
    LEFT JOIN users u_pelulus ON c.pegawai_pelulus_id = u_pelulus.id
    LEFT JOIN users u_completed ON c.unit_it_completed_by = u_completed.id
    WHERE c.unit_it_officer_id IS NOT NULL
    AND c.workflow_status IN ('dimajukan_unit_aset', 'dalam_semakan_unit_aset', 'dimajukan_pegawai_pelulus', 'diluluskan', 'selesai')
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

$query .= " ORDER BY c.unit_it_assigned_at DESC";

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
    <title>Senarai Aduan - Unit IT / Sokongan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
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
            <p class="text-gray-600 mt-2">Aduan yang memerlukan tindakan Unit IT / Sokongan</p>
        </div>

        <!-- Filter and Search -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                        <option value="dimajukan_unit_it" <?php echo $status_filter === 'dimajukan_unit_it' ? 'selected' : ''; ?>>Perlu Tindakan</option>
                        <option value="selesai" <?php echo $status_filter === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                    </select>
                </div>

                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="No. Tiket, Perkara, Pengadu..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>

                <!-- Submit -->
                <div class="flex items-end">
                    <button type="submit" class="w-full px-6 py-2 gradient-bg text-white rounded-lg hover:opacity-90 transition">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                </div>
            </form>
        </div>

        <!-- Complaints Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <?php if (count($complaints) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiket</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perkara</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengadu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ditugaskan Kepada</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarikh</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($complaints as $complaint): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-blue-600"><?php echo htmlspecialchars($complaint['ticket_number']); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($complaint['perkara']); ?></p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?php echo htmlspecialchars(substr($complaint['keterangan'], 0, 60)); ?>...
                                </p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($complaint['nama_pengadu']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($complaint['bahagian']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                    <?php echo htmlspecialchars($complaint['assigned_officer_name']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $status_colors = [
                                    'dimajukan_unit_it' => 'bg-yellow-100 text-yellow-800',
                                    'selesai' => 'bg-green-100 text-green-800'
                                ];
                                $status_labels = [
                                    'dimajukan_unit_it' => 'Perlu Tindakan',
                                    'selesai' => 'Selesai'
                                ];
                                $color = $status_colors[$complaint['workflow_status']] ?? 'bg-gray-100 text-gray-800';
                                $label = $status_labels[$complaint['workflow_status']] ?? $complaint['workflow_status'];
                                ?>
                                <span class="px-2 py-1 text-xs rounded-full font-medium <?php echo $color; ?>">
                                    <?php echo $label; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php
                                if ($complaint['workflow_status'] === 'selesai' && !empty($complaint['unit_it_completed_at'])) {
                                    echo date('d/m/Y H:i', strtotime($complaint['unit_it_completed_at']));
                                } else {
                                    echo date('d/m/Y H:i', strtotime($complaint['unit_it_assigned_at']));
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="view_complaint.php?id=<?php echo $complaint['id']; ?>"
                                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                    <i class="fas fa-eye mr-1"></i>Lihat
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                <p class="text-gray-500">Tiada aduan dijumpai</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Summary -->
        <div class="mt-6 text-sm text-gray-600">
            Jumlah: <span class="font-semibold"><?php echo count($complaints); ?></span> aduan
        </div>
    </div>
</body>
</html>
