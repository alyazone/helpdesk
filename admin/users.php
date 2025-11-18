<?php
/**
 * Admin - User Management
 */

require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.html?redirect=admin');
}

$db = getDB();

// Get filter parameters
$role_filter = $_GET['role'] ?? 'all';
$status_filter = $_GET['status'] ?? 'all';
$search = sanitize($_GET['search'] ?? '');

// Build query
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($role_filter !== 'all') {
    $sql .= " AND role = ?";
    $params[] = $role_filter;
}

if ($status_filter !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $sql .= " AND (nama_penuh LIKE ? OR email LIKE ? OR jawatan LIKE ? OR bahagian LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get statistics
$stmt = $db->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$role_counts = [];
foreach ($stmt->fetchAll() as $row) {
    $role_counts[$row['role']] = $row['count'];
}

$user = getUser();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengurusan Pengguna - Admin</title>
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
                    <span class="font-semibold text-lg">Pengurusan Pengguna</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="complaints.php" class="hover:opacity-80">
                        <i class="fas fa-clipboard-list mr-2"></i>Aduan
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
        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Jumlah Pengguna</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo count($users); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Admin</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $role_counts['admin'] ?? 0; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shield-alt text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Staff</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $role_counts['staff'] ?? 0; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-tie text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Pengguna Biasa</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $role_counts['user'] ?? 0; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user text-green-600 text-xl"></i>
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
                            placeholder="Cari (Nama, Email, Jawatan, Bahagian)"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <select name="role" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="all" <?php echo $role_filter === 'all' ? 'selected' : ''; ?>>Semua Peranan</option>
                            <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="staff" <?php echo $role_filter === 'staff' ? 'selected' : ''; ?>>Staff</option>
                            <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>User</option>
                        </select>
                    </div>
                    <div>
                        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                            <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Tidak Aktif</option>
                        </select>
                    </div>
                    <button type="submit" class="px-6 py-2 gradient-bg text-white rounded-lg hover:opacity-90">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                    <a href="users.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-redo mr-2"></i>Reset
                    </a>
                </form>
                <button onclick="openAddUserModal()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-plus mr-2"></i>Tambah Pengguna
                </button>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                Senarai Pengguna (<?php echo count($users); ?>)
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Nama</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Email</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Jawatan</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Bahagian</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Peranan</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">
                                <i class="fas fa-users text-4xl mb-2"></i>
                                <p>Tiada pengguna dijumpai</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                                            <?php echo strtoupper(substr($u['nama_penuh'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="font-medium"><?php echo htmlspecialchars($u['nama_penuh']); ?></div>
                                            <?php if (!empty($u['no_sambungan'])): ?>
                                            <div class="text-xs text-gray-500">Ext: <?php echo htmlspecialchars($u['no_sambungan']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-sm"><?php echo htmlspecialchars($u['email']); ?></td>
                                <td class="py-3 px-4 text-sm"><?php echo htmlspecialchars($u['jawatan'] ?? '-'); ?></td>
                                <td class="py-3 px-4 text-sm"><?php echo htmlspecialchars($u['bahagian'] ?? '-'); ?></td>
                                <td class="py-3 px-4">
                                    <?php
                                    $role_colors = [
                                        'admin' => 'bg-red-100 text-red-800',
                                        'staff' => 'bg-blue-100 text-blue-800',
                                        'user' => 'bg-green-100 text-green-800'
                                    ];
                                    $color = $role_colors[$u['role']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $color; ?>">
                                        <?php echo ucfirst($u['role']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <?php
                                    $status_color = $u['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $status_color; ?>">
                                        <?php echo $u['status'] === 'active' ? 'Aktif' : 'Tidak Aktif'; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex gap-2">
                                        <button onclick='openEditUserModal(<?php echo json_encode($u); ?>)'
                                               class="text-blue-600 hover:text-blue-800" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <button onclick="deleteUser(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['nama_penuh']); ?>')"
                                               class="text-red-600 hover:text-red-800" title="Padam">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
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

    <!-- Add/Edit User Modal -->
    <div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="gradient-bg text-white p-6 rounded-t-xl">
                <h2 id="modalTitle" class="text-2xl font-bold">Tambah Pengguna Baru</h2>
            </div>
            <form id="userForm" class="p-6 space-y-4">
                <input type="hidden" id="userId" name="user_id">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Penuh <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nama_penuh" name="nama_penuh" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <div id="passwordField">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Kata Laluan <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="password" name="password"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <p class="text-xs text-gray-500 mt-1">Minimum 6 aksara</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jawatan</label>
                        <input type="text" id="jawatan" name="jawatan"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">No. Sambungan</label>
                        <input type="text" id="no_sambungan" name="no_sambungan"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Bahagian</label>
                    <input type="text" id="bahagian" name="bahagian"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Peranan <span class="text-red-500">*</span>
                        </label>
                        <select id="role" name="role" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="user">User</option>
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select id="status" name="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-4 pt-4 border-t border-gray-200">
                    <button type="submit"
                            class="flex-1 px-6 py-3 gradient-bg text-white rounded-lg hover:opacity-90 transition font-semibold">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                    <button type="button" onclick="closeUserModal()"
                            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddUserModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Pengguna Baru';
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('passwordField').style.display = 'block';
            document.getElementById('password').required = true;
            document.getElementById('userModal').classList.remove('hidden');
            document.getElementById('userModal').classList.add('flex');
        }

        function openEditUserModal(user) {
            document.getElementById('modalTitle').textContent = 'Edit Pengguna';
            document.getElementById('userId').value = user.id;
            document.getElementById('nama_penuh').value = user.nama_penuh;
            document.getElementById('email').value = user.email;
            document.getElementById('jawatan').value = user.jawatan || '';
            document.getElementById('no_sambungan').value = user.no_sambungan || '';
            document.getElementById('bahagian').value = user.bahagian || '';
            document.getElementById('role').value = user.role;
            document.getElementById('status').value = user.status;
            document.getElementById('passwordField').style.display = 'none';
            document.getElementById('password').required = false;
            document.getElementById('userModal').classList.remove('hidden');
            document.getElementById('userModal').classList.add('flex');
        }

        function closeUserModal() {
            document.getElementById('userModal').classList.add('hidden');
            document.getElementById('userModal').classList.remove('flex');
        }

        document.getElementById('userForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            try {
                const response = await fetch('../api/admin/manage_user.php', {
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

        async function deleteUser(userId, userName) {
            if (!confirm(`Adakah anda pasti mahu memadam pengguna "${userName}"?`)) {
                return;
            }

            try {
                const response = await fetch('../api/admin/manage_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&user_id=${userId}`
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
        document.getElementById('userModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUserModal();
            }
        });
    </script>
</body>
</html>
