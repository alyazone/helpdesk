<?php
/**
 * Admin - All Complaints List
 */

require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.html?redirect=admin');
}

$db = getDB();

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search = sanitize($_GET['search'] ?? '');

// Build query
$sql = "
    SELECT c.*, o.nama as officer_name
    FROM complaints c
    LEFT JOIN officers o ON c.officer_id = o.id
    WHERE 1=1
";

$params = [];

if ($status_filter !== 'all') {
    $sql .= " AND c.status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $sql .= " AND (c.ticket_number LIKE ? OR c.perkara LIKE ? OR c.nama_pengadu LIKE ? OR c.email LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$sql .= " ORDER BY c.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$complaints = $stmt->fetchAll();

$user = getUser();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senarai Aduan - Admin</title>
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
                    <a href="index.php" class="flex items-center space-x-2 hover:opacity-80">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali</span>
                    </a>
                    <span class="font-semibold text-lg">Senarai Aduan</span>
                </div>
                <div class="flex items-center space-x-4">
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Cari (No. Tiket, Perkara, Nama, Emel)"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <div>
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="dalam_pemeriksaan" <?php echo $status_filter === 'dalam_pemeriksaan' ? 'selected' : ''; ?>>Dalam Pemeriksaan</option>
                        <option value="sedang_dibaiki" <?php echo $status_filter === 'sedang_dibaiki' ? 'selected' : ''; ?>>Sedang Dibaiki</option>
                        <option value="selesai" <?php echo $status_filter === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="dibatalkan" <?php echo $status_filter === 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                </div>
                <button type="submit" class="px-6 py-2 gradient-bg text-white rounded-lg hover:opacity-90">
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
                <a href="complaints.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </form>
        </div>

        <!-- Complaints Table -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                Jumlah: <?php echo count($complaints); ?> aduan
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">No. Tiket</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Jenis</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Perkara</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Pengadu</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Tarikh</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($complaints)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2"></i>
                                <p>Tiada aduan dijumpai</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($complaints as $complaint): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-purple-600"><?php echo htmlspecialchars($complaint['ticket_number']); ?></span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded text-xs font-semibold <?php echo $complaint['jenis'] === 'aduan' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'; ?>">
                                        <?php echo ucfirst($complaint['jenis']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars(substr($complaint['perkara'], 0, 40)); ?>...</td>
                                <td class="py-3 px-4">
                                    <div class="text-sm">
                                        <div class="font-medium"><?php echo htmlspecialchars($complaint['nama_pengadu']); ?></div>
                                        <div class="text-gray-500"><?php echo htmlspecialchars($complaint['email']); ?></div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <?php
                                    $status_colors = [
                                        'pending' => 'bg-gray-100 text-gray-800',
                                        'dalam_pemeriksaan' => 'bg-blue-100 text-blue-800',
                                        'sedang_dibaiki' => 'bg-yellow-100 text-yellow-800',
                                        'selesai' => 'bg-green-100 text-green-800',
                                        'dibatalkan' => 'bg-red-100 text-red-800'
                                    ];
                                    $color = $status_colors[$complaint['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $color; ?>">
                                        <?php echo str_replace('_', ' ', ucfirst($complaint['status'])); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-sm"><?php echo date('d/m/Y H:i', strtotime($complaint['created_at'])); ?></td>
                                <td class="py-3 px-4">
                                    <a href="view_complaint.php?id=<?php echo $complaint['id']; ?>"
                                       class="text-purple-600 hover:text-purple-800 mr-3" title="Lihat">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_complaint.php?id=<?php echo $complaint['id']; ?>"
                                       class="text-blue-600 hover:text-blue-800" title="Edit">
                                        <i class="fas fa-edit"></i>
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
