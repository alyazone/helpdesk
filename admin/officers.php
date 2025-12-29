<?php
/**
 * Admin - Officer Management
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
$sql = "SELECT o.*,
        (SELECT COUNT(*) FROM complaints WHERE officer_id = o.id) as total_complaints,
        (SELECT COUNT(*) FROM complaints WHERE officer_id = o.id AND status NOT IN ('selesai', 'dibatalkan')) as active_complaints
        FROM officers o
        WHERE 1=1";
$params = [];

if ($status_filter !== 'all') {
    $sql .= " AND o.status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $sql .= " AND (o.nama LIKE ? OR o.email LIKE ? OR o.no_telefon LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$officers = $stmt->fetchAll();

// Get statistics
$stmt = $db->query("SELECT COUNT(*) as total FROM officers WHERE status = 'bertugas'");
$active_officers = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM officers WHERE status = 'tidak_bertugas'");
$inactive_officers = $stmt->fetch()['total'];

$user = getUser();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengurusan Pegawai - Admin</title>
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
                        <span>Dashboard</span>
                    </a>
                    <span class="font-semibold text-lg">Pengurusan Pegawai</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="complaints.php" class="hover:opacity-80">
                        <i class="fas fa-clipboard-list mr-2"></i>Aduan
                    </a>
                    <a href="users.php" class="hover:opacity-80">
                        <i class="fas fa-users mr-2"></i>Pengguna
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
        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Jumlah Pegawai</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo count($officers); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-tie text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Bertugas</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $active_officers; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Tidak Bertugas</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $inactive_officers; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-gray-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Add Button -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex flex-wrap gap-4 items-center">
                <form method="GET" class="flex-1 flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="Cari (Nama, Email, No. Telefon)"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                            <option value="bertugas" <?php echo $status_filter === 'bertugas' ? 'selected' : ''; ?>>Bertugas</option>
                            <option value="tidak_bertugas" <?php echo $status_filter === 'tidak_bertugas' ? 'selected' : ''; ?>>Tidak Bertugas</option>
                        </select>
                    </div>
                    <button type="submit" class="px-6 py-2 gradient-bg text-white rounded-lg hover:opacity-90">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                    <a href="officers.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-redo mr-2"></i>Reset
                    </a>
                </form>
                <button onclick="openAddOfficerModal()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-plus mr-2"></i>Tambah Pegawai
                </button>
            </div>
        </div>

        <!-- Officers Table -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                Senarai Pegawai (<?php echo count($officers); ?>)
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Nama</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Email</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">No. Telefon</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Jumlah Aduan</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Aktif</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($officers)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">
                                <i class="fas fa-user-tie text-4xl mb-2"></i>
                                <p>Tiada pegawai dijumpai</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($officers as $officer): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                                            <?php echo strtoupper(substr($officer['nama'], 0, 1)); ?>
                                        </div>
                                        <div class="font-medium"><?php echo htmlspecialchars($officer['nama']); ?></div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-sm"><?php echo htmlspecialchars($officer['email'] ?? '-'); ?></td>
                                <td class="py-3 px-4 text-sm"><?php echo htmlspecialchars($officer['no_telefon'] ?? '-'); ?></td>
                                <td class="py-3 px-4">
                                    <?php
                                    $status_color = $officer['status'] === 'bertugas' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $status_color; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $officer['status'])); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-purple-600"><?php echo $officer['total_complaints']; ?></span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-blue-600"><?php echo $officer['active_complaints']; ?></span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex gap-2">
                                        <button onclick='openEditOfficerModal(<?php echo json_encode($officer); ?>)'
                                               class="text-blue-600 hover:text-blue-800" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteOfficer(<?php echo $officer['id']; ?>, '<?php echo htmlspecialchars($officer['nama']); ?>')"
                                               class="text-red-600 hover:text-red-800" title="Padam">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Officer Modal -->
    <div id="officerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4">
            <div class="gradient-bg text-white p-6 rounded-t-xl">
                <h2 id="modalTitle" class="text-2xl font-bold">Tambah Pegawai Baru</h2>
            </div>
            <form id="officerForm" class="p-6 space-y-4">
                <input type="hidden" id="officerId" name="officer_id">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Pegawai <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nama" name="nama" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Email
                    </label>
                    <input type="email" id="email" name="email"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        No. Telefon
                    </label>
                    <input type="text" id="no_telefon" name="no_telefon"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="bertugas">Bertugas</option>
                        <option value="tidak_bertugas">Tidak Bertugas</option>
                    </select>
                </div>

                <div class="flex gap-4 pt-4 border-t border-gray-200">
                    <button type="submit"
                            class="flex-1 px-6 py-3 gradient-bg text-white rounded-lg hover:opacity-90 transition font-semibold">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                    <button type="button" onclick="closeOfficerModal()"
                            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddOfficerModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Pegawai Baru';
            document.getElementById('officerForm').reset();
            document.getElementById('officerId').value = '';
            document.getElementById('officerModal').classList.remove('hidden');
            document.getElementById('officerModal').classList.add('flex');
        }

        function openEditOfficerModal(officer) {
            document.getElementById('modalTitle').textContent = 'Edit Pegawai';
            document.getElementById('officerId').value = officer.id;
            document.getElementById('nama').value = officer.nama;
            document.getElementById('email').value = officer.email || '';
            document.getElementById('no_telefon').value = officer.no_telefon || '';
            document.getElementById('status').value = officer.status;
            document.getElementById('officerModal').classList.remove('hidden');
            document.getElementById('officerModal').classList.add('flex');
        }

        function closeOfficerModal() {
            document.getElementById('officerModal').classList.add('hidden');
            document.getElementById('officerModal').classList.remove('flex');
        }

        document.getElementById('officerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            try {
                const response = await fetch('../api/admin/manage_officer.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert('Ralat: ' + result.message);
                }
            } catch (error) {
                alert('Ralat: ' + error.message);
            }
        });

        async function deleteOfficer(officerId, officerName) {
            if (!confirm(`Adakah anda pasti mahu memadam pegawai "${officerName}"?\n\nAmaran: Pegawai yang telah ditugaskan kepada aduan tidak boleh dipadam.`)) {
                return;
            }

            try {
                const response = await fetch('../api/admin/manage_officer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&officer_id=${officerId}`
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert('Ralat: ' + result.message);
                }
            } catch (error) {
                alert('Ralat: ' + error.message);
            }
        }

        // Close modal when clicking outside
        document.getElementById('officerModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOfficerModal();
            }
        });
    </script>
</body>
</html>
