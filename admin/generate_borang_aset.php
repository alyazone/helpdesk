<?php
/**
 * Admin - Generate Borang Kerosakan Aset Alih
 * Generates the asset damage form
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
           dua.no_rujukan_fail
    FROM complaints c
    LEFT JOIN users u_verified ON c.unit_aduan_verified_by = u_verified.id
    LEFT JOIN dokumen_unit_aduan dua ON c.id = dua.complaint_id
    WHERE c.id = ?
");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    redirect('complaints.php');
}

// Get borang kerosakan aset
$stmt = $db->prepare("SELECT * FROM borang_kerosakan_aset WHERE complaint_id = ? LIMIT 1");
$stmt->execute([$complaint_id]);
$borang = $stmt->fetch();

// Get approval officer if exists
$approval_officer = null;
if (!empty($complaint['approval_officer_id'])) {
    $stmt = $db->prepare("SELECT nama_penuh, email FROM users WHERE id = ?");
    $stmt->execute([$complaint['approval_officer_id']]);
    $approval_officer = $stmt->fetch();
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
            font-size: 10pt;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
        }
        .logo-placeholder {
            margin-bottom: 10px;
            font-size: 9pt;
            color: #666;
        }
        h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }
        .section-title {
            font-weight: bold;
            background-color: #e0e0e0;
            padding: 8px;
            margin: 15px 0 10px 0;
            border: 1px solid #000;
            font-size: 10pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        table td {
            padding: 8px;
            border: 1px solid #000;
            vertical-align: top;
        }
        .label-cell {
            width: 35%;
            background-color: #f5f5f5;
            font-weight: normal;
        }
        .value-cell {
            width: 65%;
        }
        .signature-box {
            border: 1px solid #000;
            padding: 15px;
            margin: 10px 0;
            min-height: 80px;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
            text-align: center;
        }
        .status-box {
            border: 2px solid #000;
            padding: 10px;
            margin: 15px 0;
            background-color: #f9f9f9;
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
        <div class="logo-placeholder">[Logo Kerajaan]</div>
        <h1>BORANG ADUAN KEROSAKAN ASET ALIH</h1>
        <div style="font-size: 9pt; margin-top: 5px;">Kew.PA-10</div>
    </div>

    <div class="section-title">Bahagian I (Untuk diisi oleh Pengadu)</div>

    <table>
        <tr>
            <td class="label-cell">1. Jenis Aset</td>
            <td class="value-cell"><?php echo htmlspecialchars($borang['jenis_aset'] ?? $complaint['jenis_aset'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td class="label-cell">2. No.Siri Pendaftaran Aset</td>
            <td class="value-cell"><?php echo htmlspecialchars($borang['no_siri_pendaftaran_aset'] ?? $complaint['no_pendaftaran_aset'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td class="label-cell">3. Pengguna Terakhir</td>
            <td class="value-cell"><?php echo htmlspecialchars($borang['pengguna_terakhir'] ?? $complaint['pengguna_akhir'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td class="label-cell">4. Tarikh Kerosakan</td>
            <td class="value-cell"><?php echo !empty($borang['tarikh_kerosakan']) ? date('d-m-Y', strtotime($borang['tarikh_kerosakan'])) : (!empty($complaint['tarikh_kerosakan']) ? date('d-m-Y', strtotime($complaint['tarikh_kerosakan'])) : '-'); ?></td>
        </tr>
        <tr>
            <td class="label-cell">5. Perihal Kerosakan</td>
            <td class="value-cell"><?php echo htmlspecialchars($borang['perihal_kerosakan'] ?? $complaint['perihal_kerosakan'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td class="label-cell">6. Nama dan Jawatan</td>
            <td class="value-cell"><?php echo htmlspecialchars($borang['nama_jawatan_pengadu'] ?? $complaint['nama_pengadu'] . ' (' . $complaint['jawatan'] . ')' ?? '-'); ?></td>
        </tr>
        <tr>
            <td class="label-cell">7. Tarikh</td>
            <td class="value-cell"><?php echo !empty($borang['tarikh_pengadu']) ? date('d-m-Y', strtotime($borang['tarikh_pengadu'])) : date('d-m-Y', strtotime($complaint['created_at'])); ?></td>
        </tr>
    </table>

    <div class="section-title">Bahagian II (Untuk diisi oleh Pegawai Aset/Pegawai Teknikal)</div>

    <table>
        <tr>
            <td class="label-cell">8. Jumlah Kos Penyelenggaraan Terdahulu</td>
            <td class="value-cell">
                <?php if (!empty($borang['jumlah_kos_penyelenggaraan_terdahulu'])): ?>
                    RM <?php echo number_format($borang['jumlah_kos_penyelenggaraan_terdahulu'], 2); ?>
                <?php else: ?>
                    RM 0.00
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td class="label-cell">9. Anggaran Kos Penyelenggaraan</td>
            <td class="value-cell">
                <?php if (!empty($borang['anggaran_kos_penyelenggaraan'])): ?>
                    RM <?php echo number_format($borang['anggaran_kos_penyelenggaraan'], 2); ?>
                <?php else: ?>
                    RM 0.00
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td class="label-cell">10. Nama dan Jawatan</td>
            <td class="value-cell"><?php echo htmlspecialchars($borang['nama_jawatan_pegawai_aset'] ?? 'PN. MAZNAH BINTI MARZUKI (Pembantu Tadbir)'); ?></td>
        </tr>
        <tr>
            <td class="label-cell">11. Tarikh</td>
            <td class="value-cell"><?php echo !empty($borang['tarikh_pegawai_aset']) ? date('d-m-Y', strtotime($borang['tarikh_pegawai_aset'])) : '-'; ?></td>
        </tr>
        <tr>
            <td class="label-cell">12. Syor Dan Ulasan</td>
            <td class="value-cell">
                <?php
                if (!empty($borang['syor_ulasan'])) {
                    echo nl2br(htmlspecialchars($borang['syor_ulasan']));
                } else {
                    echo 'Aduan telah dipanjangkan ke Pertubuhan Peladang Negeri Selangor dan akan diambil tindakan dengan kadar segera.';
                }
                ?>
            </td>
        </tr>
    </table>

    <div class="section-title">Bahagian III (Keputusan Ketua Jabatan/Bahagian/Seksyen/Unit)</div>

    <?php if (!empty($borang)): ?>
    <div class="status-box">
        <table style="border: none;">
            <tr>
                <td style="border: none; width: 15%;">Status</td>
                <td style="border: none;">
                    : <strong>
                    <?php
                    if (!empty($borang['keputusan_status'])) {
                        echo ucfirst(htmlspecialchars($borang['keputusan_status']));
                    } else {
                        echo 'Pending';
                    }
                    ?>
                    </strong>
                </td>
            </tr>
            <tr>
                <td style="border: none; vertical-align: top;">Ulasan</td>
                <td style="border: none;">
                    : <?php echo !empty($borang['keputusan_ulasan']) ? nl2br(htmlspecialchars($borang['keputusan_ulasan'])) : '-'; ?>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 20px;">
        <p>Telah memaklumkan kepada pihak PKNS untuk menukar 'eye ball' tersebut dengan kadar segera.</p>
    </div>
    <?php else: ?>
    <div style="padding: 20px; text-align: center; color: #666;">
        <p>Menunggu keputusan dari Ketua Jabatan/Bahagian</p>
    </div>
    <?php endif; ?>

    <div style="margin-top: 30px;">
        <div style="text-align: center;">
            <div style="margin-top: 50px; border-top: 1px solid #000; width: 250px; display: inline-block; padding-top: 5px;">
                Tandatangan:<br>
                Dijana komputer.
            </div>
        </div>

        <table style="border: none; margin-top: 30px;">
            <tr>
                <td style="border: none; width: 50%;">
                    Nama
                </td>
                <td style="border: none;">
                    : <?php echo htmlspecialchars($borang['keputusan_nama'] ?? 'PN ALIA BINTI MOHD YUSOF'); ?>
                </td>
            </tr>
            <tr>
                <td style="border: none;">
                    Jawatan
                </td>
                <td style="border: none;">
                    : <?php echo htmlspecialchars($borang['keputusan_jawatan'] ?? 'Penolong Pengarah'); ?>
                </td>
            </tr>
            <tr>
                <td style="border: none;">
                    Tarikh
                </td>
                <td style="border: none;">
                    : <?php echo !empty($borang['keputusan_tarikh']) ? date('d-m-Y', strtotime($borang['keputusan_tarikh'])) : date('d-m-Y'); ?>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 40px; text-align: right; font-size: 9pt;">
        <p>Borang Unit Aset</p>
        <p>No. Tiket: <?php echo htmlspecialchars($complaint['ticket_number']); ?></p>
        <p>Tarikh Jana: <?php echo date('d-m-Y H:i'); ?></p>
    </div>
</body>
</html>
