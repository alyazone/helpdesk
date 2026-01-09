<?php
/**
 * Unit Korporat - Reports Page
 * Generate various reports from complaints data
 */

require_once __DIR__ . '/../../config/config.php';

// Check if user is logged in and has Unit Korporat role
if (!isLoggedIn() || !isUnitKorporat()) {
    redirect('../../login.html');
}

$user = getUser();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Unit Korporat</title>
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
                        <a class="nav-link" href="complaints.php">Senarai Aduan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="reports.php">Laporan</a>
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
        <h2 class="mb-4"><i class="bi bi-file-earmark-bar-graph"></i> Jana Laporan</h2>

        <div class="row">
            <!-- Monthly Summary Report -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar-month"></i> Laporan Bulanan</h5>
                    </div>
                    <div class="card-body">
                        <p>Ringkasan aduan mengikut bulan, termasuk statistik status dan kategori.</p>
                        <form method="GET" action="../../api/reports/generate_monthly_report.php" target="_blank">
                            <div class="mb-3">
                                <label class="form-label">Bulan:</label>
                                <input type="month" name="month" class="form-control" value="<?php echo date('Y-m'); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-file-pdf"></i> Jana PDF
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Category Report -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-tags"></i> Laporan Mengikut Kategori</h5>
                    </div>
                    <div class="card-body">
                        <p>Analisis aduan berdasarkan kategori dalam tempoh yang dipilih.</p>
                        <form method="GET" action="../../api/reports/generate_category_report.php" target="_blank">
                            <div class="mb-3">
                                <label class="form-label">Tarikh Mula:</label>
                                <input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-01'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tarikh Tamat:</label>
                                <input type="date" name="end_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-file-excel"></i> Jana Excel
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Status Report -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-clipboard-data"></i> Laporan Status</h5>
                    </div>
                    <div class="card-body">
                        <p>Ringkasan aduan mengikut status semasa.</p>
                        <form method="GET" action="../../api/reports/generate_status_report.php" target="_blank">
                            <div class="mb-3">
                                <label class="form-label">Tarikh Mula:</label>
                                <input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-01'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tarikh Tamat:</label>
                                <input type="date" name="end_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-file-pdf"></i> Jana PDF
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Priority Report -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Laporan Keutamaan</h5>
                    </div>
                    <div class="card-body">
                        <p>Analisis aduan berdasarkan tahap keutamaan.</p>
                        <form method="GET" action="../../api/reports/generate_priority_report.php" target="_blank">
                            <div class="mb-3">
                                <label class="form-label">Tarikh Mula:</label>
                                <input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-01'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tarikh Tamat:</label>
                                <input type="date" name="end_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-file-excel"></i> Jana Excel
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Department Report -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-building"></i> Laporan Mengikut Bahagian</h5>
                    </div>
                    <div class="card-body">
                        <p>Ringkasan aduan mengikut bahagian/unit pemohon.</p>
                        <form method="GET" action="../../api/reports/generate_department_report.php" target="_blank">
                            <div class="mb-3">
                                <label class="form-label">Tarikh Mula:</label>
                                <input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-01'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tarikh Tamat:</label>
                                <input type="date" name="end_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-info">
                                <i class="bi bi-file-pdf"></i> Jana PDF
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Custom Export -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-download"></i> Eksport Tersuai</h5>
                    </div>
                    <div class="card-body">
                        <p>Eksport data aduan dengan penapis tersuai.</p>
                        <form method="GET" action="../../api/reports/custom_export.php" target="_blank">
                            <div class="mb-3">
                                <label class="form-label">Format:</label>
                                <select name="format" class="form-select" required>
                                    <option value="csv">CSV</option>
                                    <option value="excel">Excel</option>
                                    <option value="pdf">PDF</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tarikh Mula:</label>
                                <input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-01'); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tarikh Tamat:</label>
                                <input type="date" name="end_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <button type="submit" class="btn btn-secondary">
                                <i class="bi bi-download"></i> Eksport
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info mt-4">
            <i class="bi bi-lightbulb"></i>
            <strong>Petua:</strong> Semua laporan dijana berdasarkan data terkini dalam sistem. Pastikan tarikh dipilih dengan betul untuk mendapatkan laporan yang tepat.
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/role-switcher.js"></script>
</body>
</html>
