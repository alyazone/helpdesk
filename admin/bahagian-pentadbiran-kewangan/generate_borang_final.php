<?php
/**
 * Bahagian Pentadbiran & Kewangan - Generate Final Borang
 * Generates final printable version of Borang Kerosakan Aset Alih with all sections
 */

require_once __DIR__ . '/../../config/config.php';

// Allow both Unit Aset and Bahagian Pentadbiran & Kewangan to access this
if (!isLoggedIn() || (!isUnitAset() && !isBahagianPentadbiranKewangan())) {
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
           u_verified.nama_penuh as unit_aduan_officer,
           u_unit_aset.nama_penuh as unit_aset_officer,
           u_pelulus.nama_penuh as pegawai_pelulus_nama,
           dua.no_rujukan_fail
    FROM complaints c
    LEFT JOIN users u_verified ON c.unit_aduan_verified_by = u_verified.id
    LEFT JOIN users u_unit_aset ON c.unit_aset_processed_by = u_unit_aset.id
    LEFT JOIN users u_pelulus ON c.pegawai_pelulus_id = u_pelulus.id
    LEFT JOIN dokumen_unit_aduan dua ON c.id = dua.complaint_id
    WHERE c.id = ?
");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    redirect('complaints.php');
}

// Get borang kerosakan aset
$stmt = $db->prepare("SELECT * FROM borang_kerosakan_aset WHERE complaint_id = ?");
$stmt->execute([$complaint_id]);
$borang = $stmt->fetch();

if (!$borang) {
    redirect('view_complaint.php?id=' . $complaint_id);
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borang Kerosakan Aset Alih - <?php echo htmlspecialchars($complaint['ticket_number']); ?></title>
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
            font-size: 12pt;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
        }
        .logo-placeholder {
            margin-bottom: 10px;
            font-size: 10pt;
            color: #666;
        }
        h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }
        .section-title {
            font-weight: bold;
            background-color: #e0e0e0;
            padding: 8px;
            margin: 20px 0 10px 0;
            border: 1px solid #000;
            font-size: 11pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        td {
            padding: 8px;
            vertical-align: top;
        }
        .label {
            width: 40%;
            font-weight: normal;
        }
        .value {
            width: 60%;
            border-bottom: 1px solid #000;
            font-weight: bold;
        }
        .full-width {
            width: 100%;
        }
        .bordered-box {
            border: 2px solid #000;
            padding: 15px;
            margin: 15px 0;
            min-height: 100px;
        }
        .signature-area {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-block {
            display: inline-block;
            width: 45%;
            vertical-align: top;
            margin: 20px 0;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            width: 80%;
            margin: 40px auto 5px auto;
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
        .info-row {
            margin: 5px 0;
        }
        .computer-signature {
            font-family: 'Brush Script MT', cursive;
            font-size: 18pt;
            font-style: italic;
            text-align: center;
            margin: 20px 0 5px 0;
        }
        .stamp-box {
            border: 2px dashed #666;
            padding: 30px;
            text-align: center;
            margin: 10px 0;
            color: #666;
        }
        .approved-badge {
            display: inline-block;
            padding: 5px 15px;
            background-color: #10b981;
            color: white;
            font-weight: bold;
            border-radius: 5px;
            font-size: 14pt;
        }
        .rejected-badge {
            display: inline-block;
            padding: 5px 15px;
            background-color: #ef4444;
            color: white;
            font-weight: bold;
            border-radius: 5px;
            font-size: 14pt;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding: 10px 20px; background: #f59e0b; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
            <i class="fas fa-print"></i> Cetak / Simpan sebagai PDF
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-left: 10px;">
            Tutup
        </button>
    </div>

    <div class="header">
        <div class="logo-placeholder">[LOGO PLAN MALAYSIA SELANGOR]</div>
        <h1>Borang Kerosakan Aset Alih</h1>
        <p style="margin: 5px 0;">PLAN MALAYSIA - SELANGOR</p>
        <p style="margin: 5px 0; font-size: 10pt;">No. Rujukan: <?php echo htmlspecialchars($complaint['no_rujukan_fail'] ?? $complaint['ticket_number']); ?></p>
        <?php if ($borang['keputusan_status'] !== 'pending'): ?>
        <div style="margin-top: 15px;">
            <?php if ($borang['keputusan_status'] === 'diluluskan'): ?>
            <span class="approved-badge">✓ DILULUSKAN</span>
            <?php else: ?>
            <span class="rejected-badge">✗ DITOLAK</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- BAHAGIAN I: UNTUK DIISI OLEH PENGADU -->
    <div class="section-title">BAHAGIAN I: UNTUK DIISI OLEH PENGADU</div>

    <table>
        <tr>
            <td class="label">1. Jenis Aset</td>
            <td class="value"><?php echo htmlspecialchars($borang['jenis_aset'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td class="label">2. No. Siri/Pendaftaran Aset</td>
            <td class="value"><?php echo htmlspecialchars($borang['no_siri_pendaftaran_aset'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td class="label">3. Pengguna Terakhir</td>
            <td class="value"><?php echo htmlspecialchars($borang['pengguna_terakhir'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td class="label">4. Tarikh Kerosakan</td>
            <td class="value">
                <?php
                if (!empty($borang['tarikh_kerosakan'])) {
                    echo date('d/m/Y', strtotime($borang['tarikh_kerosakan']));
                } else {
                    echo '-';
                }
                ?>
            </td>
        </tr>
    </table>

    <div class="info-row">
        <strong>5. Perihal Kerosakan:</strong>
    </div>
    <div class="bordered-box">
        <?php echo nl2br(htmlspecialchars($borang['perihal_kerosakan'] ?? $complaint['perihal_kerosakan'] ?? '-')); ?>
    </div>

    <div class="signature-area">
        <table style="width: 100%;">
            <tr>
                <td style="width: 60%;">
                    <div>Nama & Jawatan:</div>
                    <div style="margin-top: 5px; border-bottom: 1px solid #000; padding-bottom: 3px;">
                        <?php echo htmlspecialchars($borang['nama_jawatan_pengadu'] ?? '-'); ?>
                    </div>
                </td>
                <td style="width: 40%; text-align: right;">
                    <div>Tarikh:</div>
                    <div style="margin-top: 5px; border-bottom: 1px solid #000; padding-bottom: 3px; display: inline-block; min-width: 150px;">
                        <?php
                        if (!empty($borang['tarikh_pengadu'])) {
                            echo date('d/m/Y', strtotime($borang['tarikh_pengadu']));
                        } else {
                            echo '-';
                        }
                        ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- BAHAGIAN II: UNTUK DIISI OLEH PEGAWAI ASET -->
    <div class="section-title" style="page-break-before: avoid; margin-top: 40px;">
        BAHAGIAN II: UNTUK DIISI OLEH PEGAWAI ASET/PEGAWAI TEKNIKAL
    </div>

    <table>
        <tr>
            <td class="label">1. Jumlah Kos Penyelenggaraan Terdahulu</td>
            <td class="value">
                RM <?php echo number_format($borang['jumlah_kos_penyelenggaraan_terdahulu'] ?? 0, 2); ?>
            </td>
        </tr>
        <tr>
            <td class="label">2. Anggaran Kos Penyelenggaraan/Pembaikan</td>
            <td class="value">
                RM <?php echo number_format($borang['anggaran_kos_penyelenggaraan'] ?? 0, 2); ?>
            </td>
        </tr>
    </table>

    <div class="info-row">
        <strong>3. Syor/Ulasan:</strong>
    </div>
    <div class="bordered-box">
        <?php echo nl2br(htmlspecialchars($borang['syor_ulasan'] ?? '-')); ?>
    </div>

    <div class="signature-area">
        <table style="width: 100%;">
            <tr>
                <td style="width: 60%;">
                    <div>Nama & Jawatan Pegawai Aset:</div>
                    <div style="margin-top: 5px; border-bottom: 1px solid #000; padding-bottom: 3px;">
                        <?php echo htmlspecialchars($borang['nama_jawatan_pegawai_aset'] ?? '-'); ?>
                    </div>
                </td>
                <td style="width: 40%; text-align: right;">
                    <div>Tarikh:</div>
                    <div style="margin-top: 5px; border-bottom: 1px solid #000; padding-bottom: 3px; display: inline-block; min-width: 150px;">
                        <?php
                        if (!empty($borang['tarikh_pegawai_aset'])) {
                            echo date('d/m/Y', strtotime($borang['tarikh_pegawai_aset']));
                        } else {
                            echo '-';
                        }
                        ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- BAHAGIAN III: KEPUTUSAN -->
    <div class="section-title" style="page-break-before: avoid; margin-top: 40px;">
        BAHAGIAN III: KEPUTUSAN KETUA JABATAN/BAHAGIAN/SEKSYEN/UNIT
    </div>

    <?php if ($borang['keputusan_status'] !== 'pending'): ?>
    <div class="info-row">
        <strong>Keputusan:</strong>
        <span style="margin-left: 20px; font-size: 14pt;">
            <?php if ($borang['keputusan_status'] === 'diluluskan'): ?>
                ☑ <strong>DILULUSKAN</strong>
                &nbsp;&nbsp;&nbsp;&nbsp;
                ☐ DITOLAK
            <?php else: ?>
                ☐ DILULUSKAN
                &nbsp;&nbsp;&nbsp;&nbsp;
                ☑ <strong>DITOLAK</strong>
            <?php endif; ?>
        </span>
    </div>

    <?php if (!empty($borang['keputusan_ulasan'])): ?>
    <div class="info-row" style="margin-top: 20px;">
        <strong>Ulasan:</strong>
    </div>
    <div class="bordered-box">
        <?php echo nl2br(htmlspecialchars($borang['keputusan_ulasan'])); ?>
    </div>
    <?php endif; ?>

    <div class="signature-area">
        <?php if ($borang['tandatangan_dijana_komputer']): ?>
        <div class="computer-signature">
            <?php echo htmlspecialchars($borang['keputusan_nama'] ?? '-'); ?>
        </div>
        <div style="text-align: center; font-size: 9pt; color: #666;">
            (Tandatangan Dijana Komputer - Sah Tanpa Tandatangan Fizikal)
        </div>
        <?php else: ?>
        <div class="signature-line"></div>
        <div style="text-align: center; margin-top: 5px;">
            (Tandatangan)
        </div>
        <?php endif; ?>

        <table style="width: 100%; margin-top: 10px;">
            <tr>
                <td style="width: 60%; text-align: center;">
                    <div>Nama & Jawatan:</div>
                    <div style="margin-top: 5px; border-bottom: 1px solid #000; padding-bottom: 3px;">
                        <?php echo htmlspecialchars($borang['keputusan_nama'] ?? '-'); ?>
                        <?php if (!empty($borang['keputusan_jawatan'])): ?>
                            <br><?php echo htmlspecialchars($borang['keputusan_jawatan']); ?>
                        <?php endif; ?>
                    </div>
                </td>
                <td style="width: 40%; text-align: center;">
                    <div>Tarikh:</div>
                    <div style="margin-top: 5px; border-bottom: 1px solid #000; padding-bottom: 3px; display: inline-block; min-width: 150px;">
                        <?php
                        if (!empty($borang['keputusan_tarikh'])) {
                            echo date('d/m/Y', strtotime($borang['keputusan_tarikh']));
                        } else {
                            echo '-';
                        }
                        ?>
                    </div>
                </td>
            </tr>
        </table>

        <div class="stamp-box">
            COP RASMI JABATAN
        </div>
    </div>

    <?php else: ?>
    <div class="bordered-box" style="min-height: 200px; text-align: center; padding-top: 80px; color: #999;">
        [ Menunggu Keputusan Pegawai Pelulus ]
    </div>
    <?php endif; ?>

    <div style="margin-top: 50px; font-size: 9pt; color: #666; border-top: 1px solid #ccc; padding-top: 10px;">
        <p><strong>Nota:</strong></p>
        <ul style="margin: 5px 0; padding-left: 20px;">
            <li>Borang ini perlu dilengkapkan dengan lengkap sebelum diserahkan kepada Pegawai Pelulus.</li>
            <li>Keputusan perlu dibuat dalam tempoh 7 hari bekerja dari tarikh penerimaan.</li>
            <li>Dokumen ini dijana secara elektronik melalui Sistem Helpdesk PLAN Malaysia Selangor.</li>
            <li>Tandatangan dijana oleh komputer adalah sah dan tidak memerlukan tandatangan fizikal.</li>
        </ul>

        <div style="margin-top: 20px; padding: 10px; background-color: #f9fafb; border: 1px solid #d1d5db; border-radius: 5px;">
            <p style="margin: 5px 0;"><strong>Maklumat Sistem:</strong></p>
            <p style="margin: 5px 0;">Tiket: <?php echo htmlspecialchars($complaint['ticket_number']); ?></p>
            <p style="margin: 5px 0;">Status Workflow: <?php echo ucfirst(str_replace('_', ' ', $complaint['workflow_status'])); ?></p>
            <p style="margin: 5px 0;">Dijana pada: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
