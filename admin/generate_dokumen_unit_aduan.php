<?php
/**
 * Admin - Generate Dokumen Unit Aduan Dalaman
 * Generates the internal complaint unit document
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
    SELECT c.*,
           u_verified.nama_penuh as unit_aduan_officer,
           uao.nama as dimajukan_ke_nama,
           uao.email as dimajukan_ke_email
    FROM complaints c
    LEFT JOIN users u_verified ON c.unit_aduan_verified_by = u_verified.id
    LEFT JOIN unit_aset_officers uao ON c.dimajukan_ke = uao.id
    WHERE c.id = ?
");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    redirect('complaints.php');
}

// Get dokumen unit aduan
$stmt = $db->prepare("SELECT * FROM dokumen_unit_aduan WHERE complaint_id = ? LIMIT 1");
$stmt->execute([$complaint_id]);
$dokumen = $stmt->fetch();

// Get officer who created the document
$created_by_name = 'System';
if (!empty($dokumen['created_by'])) {
    $stmt = $db->prepare("SELECT nama_penuh FROM users WHERE id = ?");
    $stmt->execute([$dokumen['created_by']]);
    $user = $stmt->fetch();
    if ($user) {
        $created_by_name = $user['nama_penuh'];
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumen Unit Aduan Dalaman - <?php echo htmlspecialchars($complaint['ticket_number']); ?></title>
    <style>
        @page {
            margin: 2cm;
            size: A4;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.8;
            color: #000;
            max-width: 21cm;
            margin: 0 auto;
            padding: 20px;
            font-size: 11pt;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 11pt;
            margin-bottom: 10px;
        }
        .section {
            margin: 15px 0;
        }
        .label {
            display: inline-block;
            min-width: 200px;
            vertical-align: top;
            font-weight: normal;
        }
        .value {
            display: inline-block;
            border-bottom: 1px dotted #333;
            min-width: 300px;
            padding: 0 5px;
        }
        .checkbox-group {
            margin: 10px 0;
        }
        .checkbox {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 1px solid #000;
            margin: 0 5px;
            text-align: center;
            line-height: 18px;
            vertical-align: middle;
        }
        .checkbox.checked::before {
            content: '✓';
            font-weight: bold;
            font-size: 14pt;
        }
        .text-box {
            border: 1px solid #000;
            padding: 15px;
            min-height: 100px;
            margin: 10px 0;
            background-color: #fafafa;
        }
        .signature-section {
            margin-top: 40px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 250px;
            margin: 50px auto 5px auto;
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
        <h1>UNTUK KEGUNAAN PEJABAT SAHAJA</h1>
        <div class="subtitle">Dilengkapkan oleh Pegawai Perhubungan Pelanggan:</div>
    </div>

    <div style="text-align: right; margin-bottom: 20px;">
        <strong>Tarikh: <?php echo !empty($dokumen['tarikh']) ? date('d-m-Y', strtotime($dokumen['tarikh'])) : date('d-m-Y'); ?></strong>
    </div>

    <div class="section">
        <div class="label">i. No. Rujukan Fail</div>
        <div class="value">: <?php echo htmlspecialchars($dokumen['no_rujukan_fail'] ?? $complaint['ticket_number']); ?></div>
    </div>

    <div class="section">
        <div class="label">ii. Dimajukan ke</div>
        <div class="value">: Unit Aset: <?php echo htmlspecialchars($complaint['dimajukan_ke_nama'] ?? 'Pn. Khairatun Nisak bt. Kamaruddin / PN. MAZNAH BINTI MARZUKI'); ?></div>
    </div>

    <div class="section" style="margin-top: 20px;">
        <div class="label">iii. Tindakan Susulan</div>
        <div class="checkbox-group">
            <span class="checkbox"></span> Surat/Memo
            <span class="checkbox checked" style="margin-left: 50px;"></span> Telefon
        </div>
    </div>

    <div style="margin-left: 40px; margin-top: 15px;">
        <div style="margin-bottom: 10px;">
            <span class="checkbox"></span> pertama : _______________________
        </div>
        <div style="margin-bottom: 10px;">
            <span class="checkbox"></span> kedua : _______________________
        </div>
        <div style="margin-bottom: 10px;">
            <span class="checkbox"></span> ketiga : _______________________
        </div>
    </div>

    <div class="section" style="margin-top: 30px;">
        <div class="label">iv. Tindakan / Kesimpulan :</div>
    </div>

    <div class="text-box">
        <?php
        if (!empty($dokumen['tindakan_kesimpulan'])) {
            echo nl2br(htmlspecialchars($dokumen['tindakan_kesimpulan']));
        } else {
            echo "Aduan telah diterima dan akan diambil tindakan segera";
        }
        ?>
    </div>

    <div class="signature-section">
        <div class="signature-line"></div>
        <div>Dijana oleh komputer</div>
        <div style="margin-top: 20px;">
            <strong><?php echo htmlspecialchars($created_by_name); ?></strong>
        </div>
        <div style="margin-top: 40px;">
            <div class="signature-line"></div>
            <div>Tarikh</div>
        </div>
    </div>

    <div style="margin-top: 40px; text-align: center; font-size: 9pt;">
        <p>JPBD.SEL/P104/B01</p>
        <p>No. Tiket: <?php echo htmlspecialchars($complaint['ticket_number']); ?></p>
        <p>Tarikh Jana: <?php echo date('d-m-Y H:i'); ?></p>
    </div>
</body>
</html>
