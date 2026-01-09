<?php
/**
 * Unit IT / Sokongan - Dashboard
 * PLAN Malaysia Selangor - Helpdesk System
 */

require_once __DIR__ . '/../../config/config.php';

// Allow both Unit IT Sokongan and Unit Pentadbiran roles
if (!isLoggedIn() || (!isUnitITSokongan() && !isUnitPentadbiran())) {
    redirect('../../login.html');
}

$db = getDB();
$user = getUser();

// Get statistics
$stats = [];

// Pending action (dimajukan_unit_it)
$stmt = $db->prepare("SELECT COUNT(*) as count FROM complaints WHERE workflow_status = 'dimajukan_unit_it'");
$stmt->execute();
$stats['pending'] = $stmt->fetch()['count'];

// Completed (selesai)
$stmt = $db->prepare("SELECT COUNT(*) as count FROM complaints WHERE workflow_status = 'selesai'");
$stmt->execute();
$stats['completed'] = $stmt->fetch()['count'];

// Total assigned to me
$stmt = $db->prepare("
    SELECT COUNT(*) as count
    FROM complaints c
    INNER JOIN unit_it_sokongan_officers uito ON c.unit_it_officer_id = uito.id
    INNER JOIN users u ON u.email = uito.email
    WHERE u.id = ? AND c.workflow_status IN ('dimajukan_unit_it', 'selesai')
");
$stmt->execute([$user['id']]);
$stats['my_tasks'] = $stmt->fetch()['count'];

// Get recent complaints (dimajukan_unit_it status)
$stmt = $db->prepare("
    SELECT c.*,
           bka.anggaran_kos_penyelenggaraan,
           bka.keputusan_nama as approved_by,
           bka.keputusan_tarikh as approved_date,
           uito.nama as assigned_officer_name,
           u_pelulus.nama_penuh as pelulus_name
    FROM complaints c
    LEFT JOIN borang_kerosakan_aset bka ON c.id = bka.complaint_id
    LEFT JOIN unit_it_sokongan_officers uito ON c.unit_it_officer_id = uito.id
    LEFT JOIN users u_pelulus ON c.pegawai_pelulus_id = u_pelulus.id
    WHERE c.workflow_status = 'dimajukan_unit_it'
    ORDER BY c.unit_it_assigned_at DESC
    LIMIT 10
");
$stmt->execute();
$pending_complaints = $stmt->fetchAll();

// Get my assigned complaints
$stmt = $db->prepare("
    SELECT c.*,
           bka.anggaran_kos_penyelenggaraan,
           bka.keputusan_nama as approved_by,
           bka.keputusan_tarikh as approved_date,
           uito.nama as assigned_officer_name
    FROM complaints c
    LEFT JOIN borang_kerosakan_aset bka ON c.id = bka.complaint_id
    LEFT JOIN unit_it_sokongan_officers uito ON c.unit_it_officer_id = uito.id
    INNER JOIN users u ON u.email = uito.email
    WHERE u.id = ? AND c.workflow_status = 'dimajukan_unit_it'
    ORDER BY c.unit_it_assigned_at DESC
");
$stmt->execute([$user['id']]);
$my_complaints = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Unit IT / Sokongan</title>
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
                    <i class="fas fa-tools text-2xl"></i>
                    <span class="font-semibold text-lg">Unit IT / Sokongan</span>
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
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Dashboard Unit IT / Sokongan</h1>
            <p class="text-gray-600 mt-2">Melaksanakan tindakan untuk aduan yang diluluskan</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Pending Action -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Perlu Tindakan</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['pending']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
                <a href="complaints.php?status=dimajukan_unit_it" class="text-yellow-600 text-sm font-medium mt-4 inline-block hover:underline">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <!-- My Tasks -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Tugasan Saya</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['my_tasks']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-check text-blue-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-blue-600 text-sm font-medium mt-4">Aduan yang ditugaskan kepada anda</p>
            </div>

            <!-- Completed -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Selesai</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['completed']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
                <a href="complaints.php?status=selesai" class="text-green-600 text-sm font-medium mt-4 inline-block hover:underline">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- My Assigned Tasks -->
        <?php if (count($my_complaints) > 0): ?>
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-tasks text-green-600 mr-2"></i>Tugasan Saya
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiket</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perkara</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengadu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarikh Ditugaskan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($my_complaints as $complaint): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-blue-600"><?php echo htmlspecialchars($complaint['ticket_number']); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($complaint['perkara']); ?></p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo htmlspecialchars($complaint['nama_pengadu']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo date('d/m/Y H:i', strtotime($complaint['unit_it_assigned_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="view_complaint.php?id=<?php echo $complaint['id']; ?>"
                                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                    <i class="fas fa-eye mr-1"></i>Lihat & Selesaikan
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Complaints Pending Action -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-list text-green-600 mr-2"></i>Aduan Terkini Perlu Tindakan
                </h2>
                <a href="complaints.php" class="text-green-600 hover:underline text-sm font-medium">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <?php if (count($pending_complaints) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiket</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perkara</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengadu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ditugaskan Kepada</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarikh Ditugaskan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($pending_complaints as $complaint): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-blue-600"><?php echo htmlspecialchars($complaint['ticket_number']); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($complaint['perkara']); ?></p>
                                <p class="text-xs text-gray-500">Diluluskan: <?php echo date('d/m/Y', strtotime($complaint['approved_date'])); ?></p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo htmlspecialchars($complaint['nama_pengadu']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                    <?php echo htmlspecialchars($complaint['assigned_officer_name']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo date('d/m/Y H:i', strtotime($complaint['unit_it_assigned_at'])); ?>
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
                <p class="text-gray-500">Tiada aduan yang perlu tindakan pada masa ini</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Role Switcher Component -->
    <script src="../../assets/js/role-switcher.js"></script>
</body>
</html>
