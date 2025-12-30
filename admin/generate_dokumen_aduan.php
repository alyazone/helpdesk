<?php
/**
 * Admin - Generate Dokumen Aduan
 * Generates the original complaint document
 */

require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.html?redirect=admin');
}

$db = getDB();
$complaint_id = intval($_GET['id'] ?? 0);

if ($complaint_id <= 0) {
    redirect('complaints.php');
}

// Get complaint details
$stmt = $db->prepare("
    SELECT c.*, o.nama as officer_name, o.email as officer_email,
           u.nama_penuh as user_nama
    FROM complaints c
    LEFT JOIN officers o ON c.officer_id = o.id
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.id = ?
");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    redirect('complaints.php');
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borang Aduan/Cadangan - <?php echo htmlspecialchars($complaint['ticket_number']); ?></title>
    <style>
        @page {
            margin: 2cm;
            size: A4;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #000;
            max-width: 21cm;
            margin: 0 auto;
            padding: 20px;
            font-size: 11pt;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .logo {
            width: 80px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 10pt;
            margin-bottom: 5px;
        }
        .section-title {
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 5px 8px;
            margin: 15px 0 10px 0;
            border: 1px solid #000;
            font-size: 10pt;
        }
        .form-row {
            margin-bottom: 10px;
            display: flex;
        }
        .form-label {
            width: 30%;
            font-weight: normal;
            padding-right: 10px;
        }
        .form-value {
            width: 70%;
            border-bottom: 1px solid #000;
            padding-left: 5px;
        }
        .checkbox {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 1px solid #000;
            margin-right: 5px;
            text-align: center;
            line-height: 15px;
        }
        .checkbox.checked::before {
            content: '✓';
            font-weight: bold;
        }
        .text-box {
            border: 1px solid #000;
            padding: 10px;
            min-height: 80px;
            margin: 10px 0;
        }
        .footer {
            margin-top: 30px;
            font-size: 9pt;
            font-style: italic;
        }
        .no-print {
            margin: 20px 0;
            text-align: center;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
            🖨️ Cetak Dokumen
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; margin-left: 10px;">
            ✕ Tutup
        </button>
    </div>

    <div class="header">
        <div class="subtitle">(JABATAN PERANCANGAN BANDAR DAN DESA NEGERI SELANGOR)</div>
        <h1>BORANG ADUAN/CADANGAN</h1>
        <div class="subtitle">(Botak disediakan untuk tindakan PLANMalaysia@Selangor)</div>
    </div>

    <div class="form-row">
        <div class="form-label">
            1.
            <span class="checkbox <?php echo $complaint['jenis'] === 'aduan' ? 'checked' : ''; ?>"></span> Aduan
            <span class="checkbox <?php echo $complaint['jenis'] === 'cadangan' ? 'checked' : ''; ?>"></span> Cadangan
        </div>
        <div class="form-label" style="width: 40%; text-align: right;">
            Cop Bahagian dan tarikh terima:
        </div>
    </div>

    <div class="form-row">
        <div class="form-label">2. Perkara</div>
        <div class="form-value"><?php echo htmlspecialchars($complaint['perkara']); ?></div>
    </div>

    <div class="form-row">
        <div class="form-label">3. Keterangan</div>
        <div class="form-value" style="border: none; width: 70%;">
            <div class="text-box">
                <?php echo nl2br(htmlspecialchars($complaint['keterangan'])); ?>
            </div>
        </div>
    </div>

    <div style="margin-top: 20px;">
        <div class="form-row">
            <div class="form-label">Nama</div>
            <div class="form-value"><?php echo htmlspecialchars($complaint['nama_pengadu']); ?></div>
        </div>

        <div class="form-row">
            <div class="form-label">Jawatan</div>
            <div class="form-value"><?php echo htmlspecialchars($complaint['jawatan'] ?? '-'); ?></div>
        </div>

        <div class="form-row">
            <div class="form-label">Alamat</div>
            <div class="form-value"><?php echo htmlspecialchars($complaint['bahagian'] ?? '-'); ?></div>
        </div>

        <div class="form-row">
            <div class="form-label">Poskod</div>
            <div class="form-value"><?php echo htmlspecialchars($complaint['poskod'] ?? '-'); ?></div>
        </div>

        <div class="form-row">
            <div class="form-label">No. Telefon</div>
            <div class="form-value"><?php echo htmlspecialchars($complaint['no_telefon'] ?? '-'); ?></div>
        </div>

        <div class="form-row">
            <div class="form-label">Emel</div>
            <div class="form-value"><?php echo htmlspecialchars($complaint['email']); ?></div>
        </div>

        <div class="form-row">
            <div class="form-label">Tarikh</div>
            <div class="form-value"><?php echo date('d-m-Y', strtotime($complaint['created_at'])); ?></div>
        </div>

        <div class="form-row">
            <div class="form-label">Penerima Aduan</div>
            <div class="form-value"><?php echo htmlspecialchars($complaint['officer_name'] ?? '-'); ?></div>
        </div>
    </div>

    <div class="footer">
        <p>• Setiap borang aduan / cadangan hendaklah diisi dengan lengkap.</p>
        <p>• Nyatakan tarikh kejadian, bahagian atau nama yang terlibat dan lain-lain maklumat yang boleh membantu siasatan.</p>
    </div>

    <div style="margin-top: 30px; text-align: right; font-size: 9pt;">
        <p>JPBD.SEL/P104/B01</p>
        <p>No. Rujukan: <?php echo htmlspecialchars($complaint['ticket_number']); ?></p>
        <p>Tarikh Jana: <?php echo date('d-m-Y H:i'); ?></p>
    </div>
</body>
</html>
