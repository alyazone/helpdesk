<?php
/**
 * Unit Aduan Dalaman - Generate Dokumen Unit Aduan
 * Generates the complaint document for official use
 */

require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !isUnitAduanDalaman()) {
    redirect('../../login.html');
}

$db = getDB();
$complaint_id = intval($_GET['id'] ?? 0);

if ($complaint_id <= 0) {
    redirect('complaints.php');
}

// Get complaint details
$stmt = $db->prepare("
    SELECT c.*,
           u.nama_penuh as verified_by_name,
           uao.nama as dimajukan_ke_nama,
           uao.email as dimajukan_ke_email
    FROM complaints c
    LEFT JOIN users u ON c.unit_aduan_verified_by = u.id
    LEFT JOIN unit_aset_officers uao ON c.dimajukan_ke = uao.id
    WHERE c.id = ?
");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    redirect('complaints.php');
}

// Get dokumen unit aduan
$stmt = $db->prepare("SELECT * FROM dokumen_unit_aduan WHERE complaint_id = ?");
$stmt->execute([$complaint_id]);
$dokumen = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumen Unit Aduan - <?php echo htmlspecialchars($complaint['ticket_number']); ?></title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #000;
            max-width: 21cm;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        .section {
            margin: 20px 0;
        }
        .label {
            display: inline-block;
            width: 150px;
            vertical-align: top;
        }
        .value {
            display: inline;
            font-weight: bold;
        }
        .checkbox {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 1px solid #000;
            margin-right: 5px;
            text-align: center;
        }
        .checked {
            background-color: #000;
        }
        .box {
            border: 1px solid #000;
            padding: 15px;
            margin: 15px 0;
            min-height: 150px;
        }
        table {
            width: 100%;
            margin: 10px 0;
        }
        td {
            padding: 5px;
        }
        .no-print {
            margin: 20px 0;
            text-align: center;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
        }
        .signature-section {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
            <i class="fas fa-print"></i> Cetak Dokumen
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-left: 10px;">
            Tutup
        </button>
    </div>

    <div class="header">
        <h1>UNTUK KEGUNAAN PEJABAT SAHAJA</h1>
        <p style="margin: 10px 0;">Dilengkapkan oleh Pegawai Perhubungan Pelanggan:</p>
    </div>

    <div class="section">
        <table>
            <tr>
                <td style="width: 30px;">i.</td>
                <td style="width: 200px;">No. Rujukan Fail</td>
                <td>: <?php echo htmlspecialchars($dokumen['no_rujukan_fail'] ?? $complaint['ticket_number']); ?></td>
                <td style="width: 150px; text-align: right;">Tarikh:</td>
                <td style="width: 150px;"><?php echo date('d-m-Y', strtotime($dokumen['tarikh'] ?? $complaint['created_at'])); ?></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table>
            <tr>
                <td style="width: 30px;">ii.</td>
                <td style="width: 200px;">Dimajukan ke</td>
                <td>: Unit Aset: <?php echo htmlspecialchars($complaint['dimajukan_ke_nama'] ?? '-'); ?>
                    <?php if (!empty($complaint['dimajukan_ke_nama'])): ?>
                    / <?php echo htmlspecialchars($complaint['dimajukan_ke_nama']); ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table>
            <tr>
                <td style="width: 30px;">iii.</td>
                <td style="width: 200px;">Tindakan Susulan</td>
                <td>:
                    <div class="checkbox <?php echo ($dokumen && strpos($dokumen['tindakan_susulan'], 'Surat') !== false) ? 'checked' : ''; ?>"></div> Surat/Memo
                    <span style="margin-left: 20px;">
                        <div class="checkbox <?php echo ($dokumen && strpos($dokumen['tindakan_susulan'], 'Telefon') !== false) ? 'checked' : ''; ?>"></div> Telefon
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table>
            <tr>
                <td style="width: 30px;"></td>
                <td style="width: 200px;"></td>
                <td>
                    <div class="checkbox"></div> pertama :
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>
                    <div class="checkbox"></div> kedua :
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>
                    <div class="checkbox"></div> ketiga :
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table>
            <tr>
                <td style="width: 30px;">iv.</td>
                <td style="width: 200px; vertical-align: top;">Tindakan / Kesimpulan :</td>
                <td></td>
            </tr>
        </table>

        <div class="box">
            <?php if (!empty($dokumen['tindakan_kesimpulan'])): ?>
                <?php echo nl2br(htmlspecialchars($dokumen['tindakan_kesimpulan'])); ?>
            <?php else: ?>
                <?php echo nl2br(htmlspecialchars($complaint['tindakan_susulan'] ?? 'Aduan telah diterima dan akan diambil tindakan segera')); ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="signature-section">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; text-align: center;">
                    <p>Tandatangan :</p>
                    <p style="margin-top: 60px; border-top: 1px solid #000; display: inline-block; padding: 10px 50px;">
                        Dijana komputer.
                    </p>
                    <p style="margin-top: 10px;">
                        Nama : <?php echo htmlspecialchars($complaint['verified_by_name'] ?? 'En. Azri Hanis Bin Zul'); ?>
                    </p>
                    <p>
                        Jawatan : Penolong Pengarah
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <script>
        // Auto-print on load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
