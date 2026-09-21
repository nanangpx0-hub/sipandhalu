<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$pdo = Database::connection();

echo "========================================================\n";
echo " SINKRONISASI SAMPEL SERUTI TRIWULAN 1 - 4 TAHUN 2026\n";
echo "========================================================\n";

$serutiQuarters = [
    [
        'jenis' => 'SERUTI_Q1',
        'label' => '2026-Q1 Seruti Triwulan I',
        'mulai' => '2026-01-01',
        'selesai' => '2026-03-31',
        'status' => 'DRAFT',
        'catatan' => 'Master wilayah & penugasan Seruti Triwulan I 2026 (28 NKS)',
    ],
    [
        'jenis' => 'SERUTI_Q2',
        'label' => '2026-Q2 Seruti Triwulan II',
        'mulai' => '2026-04-01',
        'selesai' => '2026-06-30',
        'status' => 'DRAFT',
        'catatan' => 'Master wilayah & penugasan Seruti Triwulan II 2026 (28 NKS)',
    ],
    [
        'jenis' => 'SERUTI_Q3',
        'label' => '2026-Q3 Seruti Triwulan III',
        'mulai' => '2026-07-01',
        'selesai' => '2026-09-30',
        'status' => 'AKTIF',
        'catatan' => 'Master wilayah & penugasan Seruti Triwulan III 2026 (integrasi Susenas S2 2026)',
    ],
    [
        'jenis' => 'SERUTI_Q4',
        'label' => '2026-Q4 Seruti Triwulan IV',
        'mulai' => '2026-10-01',
        'selesai' => '2026-12-31',
        'status' => 'DRAFT',
        'catatan' => 'Master wilayah & penugasan Seruti Triwulan IV 2026 (28 NKS)',
    ],
];

// Ambil master penugasan dari Susenas S2 (periode_id = 1) sebagai acuan penugasan 28 SLS
$stmtS2 = $pdo->query("
    SELECT sp.sls_id, s.nks, pg.pcl_id, pg.pml_id, pg.pengolah_id
    FROM sampel sp
    JOIN sls s ON s.id = sp.sls_id
    JOIN penugasan pg ON pg.sampel_id = sp.id
    WHERE sp.periode_id = 1
    ORDER BY s.nks ASC
");
$masterAssignments = $stmtS2->fetchAll(PDO::FETCH_ASSOC);

if (count($masterAssignments) !== 28) {
    echo "Peringatan: Ditemukan " . count($masterAssignments) . " penugasan di Susenas S2 (diharapkan 28).\n";
}

$stmtInsPeriode = $pdo->prepare("
    INSERT INTO periode (tahun, jenis, label, tgl_mulai, tgl_selesai, status, catatan)
    VALUES (2026, :jenis, :label, :mulai, :selesai, :status, :catatan)
    ON DUPLICATE KEY UPDATE
        label = VALUES(label),
        tgl_mulai = VALUES(tgl_mulai),
        tgl_selesai = VALUES(tgl_selesai),
        status = VALUES(status),
        catatan = VALUES(catatan)
");

$stmtGetPeriode = $pdo->prepare("SELECT id FROM periode WHERE tahun = 2026 AND jenis = :jenis LIMIT 1");

$stmtInsSampel = $pdo->prepare("
    INSERT INTO sampel (periode_id, sls_id, target_sampel, muatan_awal, dokumen_vsen, peta_ws)
    VALUES (:pid, :sls_id, 10, 50, 1, 1)
    ON DUPLICATE KEY UPDATE target_sampel = 10, dokumen_vsen = 1, peta_ws = 1
");

$stmtFindSampel = $pdo->prepare("
    SELECT id FROM sampel WHERE periode_id = :pid AND sls_id = :sls_id LIMIT 1
");

$stmtAssign = $pdo->prepare("
    INSERT INTO penugasan (sampel_id, pcl_id, pml_id, pengolah_id, status)
    VALUES (:sid, :pcl, :pml, :peng, 'AKTIF')
    ON DUPLICATE KEY UPDATE
        pcl_id = VALUES(pcl_id),
        pml_id = VALUES(pml_id),
        pengolah_id = VALUES(pengolah_id),
        status = 'AKTIF'
");

$stmtInsRuta = $pdo->prepare("
    INSERT IGNORE INTO sampel_ruta (sampel_id, no_urut_ruta, status_dokumen, status_selesai)
    VALUES (:sid, :no, 'BELUM', 'BELUM')
");

foreach ($serutiQuarters as $q) {
    $stmtInsPeriode->execute([
        ':jenis' => $q['jenis'],
        ':label' => $q['label'],
        ':mulai' => $q['mulai'],
        ':selesai' => $q['selesai'],
        ':status' => $q['status'],
        ':catatan' => $q['catatan'],
    ]);

    $stmtGetPeriode->execute([':jenis' => $q['jenis']]);
    $pid = (int) $stmtGetPeriode->fetchColumn();

    echo "\nMemproses Periode [{$pid}] {$q['label']} (Status: {$q['status']})...\n";

    $slsCount = 0;
    $rutaCount = 0;

    foreach ($masterAssignments as $ma) {
        $slsId = (int) $ma['sls_id'];
        $stmtInsSampel->execute([':pid' => $pid, ':sls_id' => $slsId]);

        $stmtFindSampel->execute([':pid' => $pid, ':sls_id' => $slsId]);
        $sampelId = (int) $stmtFindSampel->fetchColumn();

        // Assign petugas
        $stmtAssign->execute([
            ':sid' => $sampelId,
            ':pcl' => (int) $ma['pcl_id'],
            ':pml' => (int) $ma['pml_id'],
            ':peng' => (int) $ma['pengolah_id'],
        ]);
        $slsCount++;

        // Inisialisasi 10 ruta per SLS
        for ($no = 1; $no <= 10; $no++) {
            $stmtInsRuta->execute([':sid' => $sampelId, ':no' => $no]);
            $rutaCount++;
        }
    }

    echo "  -> Berhasil sinkronisasi: {$slsCount} SLS (28 NKS) dan {$rutaCount} Ruta sampel Seruti.\n";
}

echo "\n========================================================\n";
echo " SINKRONISASI 4 PERIODE SERUTI 2026 SELESAI DENGAN SUKSES!\n";
echo "========================================================\n";
