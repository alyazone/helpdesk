<?php
/**
 * Unit Korporat Dashboard
 * View-only access to complaints with reporting capabilities
 */

require_once __DIR__ . '/../../config/config.php';

// Check if user is logged in and has Unit Korporat role
if (!isLoggedIn() || !isUnitKorporat()) {
    redirect('../../login.html');
}

$db = getDB();

// Get statistics
$stats = [
    'total' => 0,
    'baru' => 0,
    'dalam_proses' => 0,
    'selesai' => 0,
    'ditolak' => 0
];

try {
    // Total complaints
    $stmt = $db->query("SELECT COUNT(*) as total FROM complaints");
    $stats['total'] = $stmt->fetch()['total'];

    // By status
    $stmt = $db->query("
        SELECT workflow_status, COUNT(*) as count
        FROM complaints
        GROUP BY workflow_status
    ");

    while ($row = $stmt->fetch()) {
        if ($row['workflow_status'] === 'baru') {
            $stats['baru'] = $row['count'];
        } elseif ($row['workflow_status'] === 'selesai') {
            $stats['selesai'] = $row['count'];
        } elseif ($row['workflow_status'] === 'ditolak') {
            $stats['ditolak'] = $row['count'];
        } else {
            $stats['dalam_proses'] += $row['count'];
        }
    }

    // Recent complaints
    $stmt = $db->query("
        SELECT c.*, u.nama_penuh as user_name
        FROM complaints c
        LEFT JOIN users u ON c.user_id = u.id
        ORDER BY c.created_at DESC
        LIMIT 10
    ");
    $recentComplaints = $stmt->fetchAll();

} catch (Exception $e) {
    error_log("Error fetching stats: " . $e->getMessage());
}

$user = getUser();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Unit Korporat - Sistem Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <style>
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .stat-card.total { border-left-color: #6f42c1; }
        .stat-card.new { border-left-color: #0d6efd; }
        .stat-card.process { border-left-color: #ffc107; }
        .stat-card.completed { border-left-color: #28a745; }
        .stat-card.rejected { border-left-color: #dc3545; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-bar-chart-line"></i> Unit Korporat (Laporan)
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="complaints.php">Senarai Aduan</a>
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
        <h2 class="mb-4">
            <i class="bi bi-speedometer2"></i> Dashboard Unit Korporat
        </h2>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Unit Korporat (Laporan):</strong> Anda mempunyai akses untuk melihat semua aduan, menjana laporan, dan mengakses statistik sistem.
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card total">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Jumlah Aduan</h6>
                                <h2 class="card-title mb-0"><?php echo $stats['total']; ?></h2>
                            </div>
                            <div class="text-purple fs-1">
                                <i class="bi bi-clipboard-data"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card stat-card new">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Aduan Baru</h6>
                                <h2 class="card-title mb-0"><?php echo $stats['baru']; ?></h2>
                            </div>
                            <div class="text-primary fs-1">
                                <i class="bi bi-file-earmark-plus"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card stat-card process">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Dalam Proses</h6>
                                <h2 class="card-title mb-0"><?php echo $stats['dalam_proses']; ?></h2>
                            </div>
                            <div class="text-warning fs-1">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card stat-card completed">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Selesai</h6>
                                <h2 class="card-title mb-0"><?php echo $stats['selesai']; ?></h2>
                            </div>
                            <div class="text-success fs-1">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Complaints -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Aduan Terkini</h5>
                    </div>
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
                                        <th>Tarikh</th>
                                        <th>Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentComplaints as $complaint): ?>
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
                                        <td><?php echo date('d/m/Y', strtotime($complaint['created_at'])); ?></td>
                                        <td>
                                            <a href="view_complaint.php?id=<?php echo $complaint['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i> Lihat
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="complaints.php" class="btn btn-primary">
                                Lihat Semua Aduan <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/role-switcher.js"></script>
</body>
</html>
