<?php
/**
 * Unit Korporat - Complaints List
 * View all complaints for reporting purposes
 */

require_once __DIR__ . '/../../config/config.php';

// Check if user is logged in and has Unit Korporat role
if (!isLoggedIn() || !isUnitKorporat()) {
    redirect('../../login.html');
}

$db = getDB();
$user = getUser();

// Filtering
$filterStatus = $_GET['status'] ?? '';
$filterCategory = $_GET['category'] ?? '';
$filterSearch = $_GET['search'] ?? '';

// Build query
$query = "
    SELECT c.*, u.nama_penuh as user_name
    FROM complaints c
    LEFT JOIN users u ON c.user_id = u.id
    WHERE 1=1
";

$params = [];

if ($filterStatus) {
    $query .= " AND c.workflow_status = ?";
    $params[] = $filterStatus;
}

if ($filterCategory) {
    $query .= " AND c.kategori_aduan = ?";
    $params[] = $filterCategory;
}

if ($filterSearch) {
    $query .= " AND (c.ticket_number LIKE ? OR c.tajuk LIKE ? OR c.keterangan LIKE ?)";
    $searchTerm = "%{$filterSearch}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$query .= " ORDER BY c.created_at DESC";

try {
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $complaints = $stmt->fetchAll();

    // Get categories for filter
    $categoriesStmt = $db->query("SELECT DISTINCT kategori_aduan FROM complaints ORDER BY kategori_aduan");
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

} catch (Exception $e) {
    error_log("Error fetching complaints: " . $e->getMessage());
    $complaints = [];
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senarai Aduan - Unit Korporat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-bar-chart-line"></i> Unit Korporat (Laporan)
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="complaints.php">Senarai Aduan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">Laporan</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user['nama']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../../semakan.html"><i class="bi bi-house"></i> Halaman Utama</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../../api/logout.php"><i class="bi bi-box-arrow-right"></i> Log Keluar</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-list-ul"></i> Senarai Aduan</h2>
            <button class="btn btn-success" onclick="window.location.href='reports.php'">
                <i class="bi bi-file-earmark-bar-graph"></i> Jana Laporan
            </button>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="baru" <?php echo $filterStatus === 'baru' ? 'selected' : ''; ?>>Baru</option>
                            <option value="disahkan_unit_aduan" <?php echo $filterStatus === 'disahkan_unit_aduan' ? 'selected' : ''; ?>>Disahkan</option>
                            <option value="dimajukan_unit_aset" <?php echo $filterStatus === 'dimajukan_unit_aset' ? 'selected' : ''; ?>>Dimajukan Unit Aset</option>
                            <option value="dalam_semakan_unit_aset" <?php echo $filterStatus === 'dalam_semakan_unit_aset' ? 'selected' : ''; ?>>Dalam Semakan</option>
                            <option value="dimajukan_pegawai_pelulus" <?php echo $filterStatus === 'dimajukan_pegawai_pelulus' ? 'selected' : ''; ?>>Menunggu Kelulusan</option>
                            <option value="diluluskan" <?php echo $filterStatus === 'diluluskan' ? 'selected' : ''; ?>>Diluluskan</option>
                            <option value="ditolak" <?php echo $filterStatus === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                            <option value="dimajukan_unit_it" <?php echo $filterStatus === 'dimajukan_unit_it' ? 'selected' : ''; ?>>Dimajukan Unit IT</option>
                            <option value="selesai" <?php echo $filterStatus === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kategori</label>
                        <select name="category" class="form-select">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $filterCategory === $category ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Carian</label>
                        <input type="text" name="search" class="form-control" placeholder="Tiket, tajuk, atau keterangan" value="<?php echo htmlspecialchars($filterSearch); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Tapis</button>
                            <a href="complaints.php" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Complaints Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tiket</th>
                                <th>Tajuk</th>
                                <th>Pengadu</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Keutamaan</th>
                                <th>Tarikh</th>
                                <th>Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($complaints)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Tiada aduan dijumpai</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($complaints as $complaint): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($complaint['ticket_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($complaint['tajuk']); ?></td>
                                    <td><?php echo htmlspecialchars($complaint['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($complaint['kategori_aduan']); ?></td>
                                    <td>
                                        <?php
                                        $statusMap = [
                                            'baru' => ['badge' => 'primary', 'text' => 'Baru'],
                                            'disahkan_unit_aduan' => ['badge' => 'info', 'text' => 'Disahkan'],
                                            'dimajukan_unit_aset' => ['badge' => 'warning', 'text' => 'Dimajukan Unit Aset'],
                                            'dalam_semakan_unit_aset' => ['badge' => 'warning', 'text' => 'Dalam Semakan'],
                                            'dimajukan_pegawai_pelulus' => ['badge' => 'warning', 'text' => 'Menunggu Kelulusan'],
                                            'diluluskan' => ['badge' => 'info', 'text' => 'Diluluskan'],
                                            'ditolak' => ['badge' => 'danger', 'text' => 'Ditolak'],
                                            'dimajukan_unit_it' => ['badge' => 'info', 'text' => 'Dimajukan Unit IT'],
                                            'selesai' => ['badge' => 'success', 'text' => 'Selesai']
                                        ];
                                        $status = $statusMap[$complaint['workflow_status']] ?? ['badge' => 'secondary', 'text' => $complaint['workflow_status']];
                                        ?>
                                        <span class="badge bg-<?php echo $status['badge']; ?>">
                                            <?php echo $status['text']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $priorityMap = [
                                            'Rendah' => 'success',
                                            'Sederhana' => 'warning',
                                            'Tinggi' => 'danger',
                                            'Kritikal' => 'dark'
                                        ];
                                        $priorityBadge = $priorityMap[$complaint['keutamaan']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $priorityBadge; ?>">
                                            <?php echo htmlspecialchars($complaint['keutamaan']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($complaint['created_at'])); ?></td>
                                    <td>
                                        <a href="view_complaint.php?id=<?php echo $complaint['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> Lihat
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <p class="text-muted">Jumlah aduan: <?php echo count($complaints); ?></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/role-switcher.js"></script>
</body>
</html>
