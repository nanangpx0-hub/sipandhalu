<?php

declare(strict_types=1);

/**
 * Seed wilayah + SLS dari snapshot Excel "Alokasi (2).xlsx" (28 SLS/RT sampel).
 * Kode 16 digit (kode_full) BELUM padan -> NULL; wajib diisi dari BPS saat pemutakhiran.
 * Idempoten. Jalankan: php database/seeds/002_wilayah_seed.php
 */

$base = dirname(__DIR__, 2);
require $base . '/vendor/autoload.php';

use App\Core\Config;

Config::loadEnv($base);
$cfg = require $base . '/config/database.php';
$pdo = new PDO(
    sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']),
    $cfg['user'],
    $cfg['pass'],
    $cfg['options']
);

$kecs = [
    ['020', 'GUMUKMAS'],
    ['030', 'PUGER'],
    ['040', 'WULUHAN'],
    ['050', 'AMBULU'],
    ['060', 'TEMPUREJO'],
    ['070', 'SILO'],
    ['090', 'MUMBULSARI'],
    ['120', 'RAMBIPUJI'],
    ['130', 'BALUNG'],
    ['140', 'UMBULSARI'],
    ['170', 'SUMBERBARU'],
    ['180', 'TANGGUL'],
    ['190', 'BANGSALSARI'],
    ['200', 'PANTI'],
    ['250', 'LEDOKOMBO'],
    ['260', 'SUMBERJAMBE'],
    ['710', 'KALIWATES'],
    ['720', 'SUMBERSARI'],
];
$desas = [
    ['020', '003', 'MENAMPU'],
    ['020', '007', 'TEMBOKREJO'],
    ['020', '008', 'KARANGREJO'],
    ['030', '001', 'MOJOMULYO'],
    ['040', '005', 'DUKUH DEMPOK'],
    ['050', '001', 'SUMBERREJO'],
    ['050', '005', 'PONTANG'],
    ['060', '005', 'SIDODADI'],
    ['070', '002', 'PACE'],
    ['070', '009', 'SIDOMULYO'],
    ['090', '005', 'MUMBULSARI'],
    ['120', '003', 'ROWOTAMTU'],
    ['130', '004', 'BALUNG KULON'],
    ['140', '001', 'SUKORENO'],
    ['170', '002', 'ROWO TENGAH'],
    ['180', '004', 'SELODAKON'],
    ['180', '007', 'PATEMON'],
    ['190', '009', 'TUGUSARI'],
    ['200', '006', 'SUCI'],
    ['250', '001', 'SUREN'],
    ['250', '002', 'SUMBER SALAK'],
    ['250', '003', 'SUMBER BULUS'],
    ['260', '008', 'PRINGGONDANI'],
    ['710', '002', 'SEMPUSARI'],
    ['710', '007', 'KEBON AGUNG'],
    ['720', '001', 'KERANJINGAN'],
    ['720', '006', 'TEGAL GEDE'],
];
$slsRows = [
    ['020', '003', '50536', 'MENAMPU', 83],
    ['020', '007', '00327', 'TEMBOKREJO', 58],
    ['020', '008', '00407', 'KARANGREJO', 89],
    ['030', '001', '00445', 'MOJOMULYO', 50],
    ['040', '005', '51611', 'DUKUH DEMPOK', 39],
    ['050', '001', '51797', 'SUMBERREJO', 59],
    ['050', '005', '52097', 'PONTANG', 92],
    ['060', '005', '52471', 'SIDODADI', 54],
    ['070', '002', '01356', 'PACE', 47],
    ['070', '009', '01692', 'SIDOMULYO', 51],
    ['090', '005', '53400', 'MUMBULSARI', 49],
    ['120', '003', '54904', 'ROWOTAMTU', 26],
    ['130', '004', '55394', 'BALUNG KULON', 74],
    ['140', '001', '02185', 'SUKORENO', 45],
    ['170', '002', '56326', 'ROWO TENGAH', 47],
    ['170', '002', '56361', 'ROWO TENGAH', 101],
    ['180', '004', '03125', 'SELODAKON', 62],
    ['180', '007', '56901', 'PATEMON', 47],
    ['190', '009', '03376', 'TUGUSARI', 62],
    ['200', '006', '57672', 'SUCI', 30],
    ['250', '001', '04098', 'SUREN', 46],
    ['250', '002', '04167', 'SUMBER SALAK', 72],
    ['250', '003', '58988', 'SUMBER BULUS', 29],
    ['260', '008', '04592', 'PRINGGONDANI', 50],
    ['710', '002', '59905', 'SEMPUSARI', 110],
    ['710', '007', '60488', 'KEBON AGUNG', 64],
    ['720', '001', '60552', 'KERANJINGAN', 45],
    ['720', '006', '61110', 'TEGAL GEDE', 73],
];

$kc = $pdo->prepare('INSERT IGNORE INTO kecamatan (kode,nama) VALUES (:k,:n)');
foreach ($kecs as $k) { $kc->execute([':k' => $k[0], ':n' => $k[1]]); }
echo "kecamatan: ", count($kecs), PHP_EOL;

$dd = $pdo->prepare('INSERT IGNORE INTO desa (kecamatan_kode, kode, nama) VALUES (:k,:d,:n)');
foreach ($desas as $d) { $dd->execute([':k' => $d[0], ':d' => $d[1], ':n' => $d[2]]); }
echo "desa: ", count($desas), PHP_EOL;

$ss = $pdo->prepare('INSERT INTO sls (kec, desa, nks, nama_sls, jml_rt) VALUES (:k,:d,:n,:m,:r) ON DUPLICATE KEY UPDATE jml_rt=VALUES(jml_rt)');
foreach ($slsRows as $s) { $ss->execute([':k' => $s[0], ':d' => $s[1], ':n' => $s[2], ':m' => $s[3], ':r' => $s[4]]); }
echo "sls: ", count($slsRows), PHP_EOL;

$s = $pdo->prepare("SELECT id FROM periode WHERE tahun=2026 AND jenis='SUSENAS_S2'");
$s->execute();
$pid = $s->fetchColumn();
if ($pid === false) {
    $pdo->prepare("INSERT INTO periode (tahun,jenis,label,status) VALUES (2026,'SUSENAS_S2','2026-S2 Susenas September','AKTIF')")->execute();
    $pid = (int) $pdo->lastInsertId();
    echo "periode 2026-S2 dibuat (ID $pid)\n";
} else {
    echo "periode 2026-S2 sudah ada (ID $pid)\n";
}

$sp = $pdo->prepare(
    'INSERT INTO sampel (periode_id, sls_id, target_sampel, muatan_awal) VALUES (:p,:s,10,:m) ON DUPLICATE KEY UPDATE muatan_awal=VALUES(muatan_awal)'
);
$n = 0;
$all = $pdo->query('SELECT id, jml_rt FROM sls ORDER BY nks')->fetchAll();
foreach ($all as $r) {
    $sp->execute([':p' => (int) $pid, ':s' => (int) $r['id'], ':m' => (int) ($r['jml_rt'] ?? 0)]);
    $n++;
}
echo "sampel periode $pid: $n SLS (target 10/sls => ", ($n*10), " KK/RT)\n";
echo "SEED WILAYAH SELESAI\n";