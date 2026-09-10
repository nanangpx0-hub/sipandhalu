<?php

declare(strict_types=1);

/**
 * Seed 004 - DATA DUMMY semua tabel untuk uji coba aplikasi SIPANDHALU.
 * Menambah: 28 PCL + 15 PML dummy, 4 user demo (must_reset=0, password Dummy3509!),
 * periode SUSENAS_S1 2026 (TUTUP, demo histori) + SERUTI_Q1 2026 (DRAFT, demo rotasi),
 * sampel + penugasan mengikuti aturan K4 (1 orang 1 peran per periode).
 * Idempoten. Jalankan: php database/seeds/004_dummy_seed.php
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

$roleId = static function (string $code) use ($pdo): int {
    $s = $pdo->prepare('SELECT id FROM roles WHERE code=:c LIMIT 1');
    $s->execute([':c' => $code]);
    $id = $s->fetchColumn();
    if ($id === false) {
        fwrite(STDERR, "Role $code tidak ada. Jalankan seed_tahap1 dulu.\n");
        exit(1);
    }
    return (int) $id;
};

// ---------------------------------------------------------------- 1) ORANG
// Pool dummy: PCL (28) + PML (15). Pengolah tetap pakai 8 orang asli (id 1-8).
$insOrang = $pdo->prepare('INSERT IGNORE INTO orang (nama) VALUES (:n)');
$pclIds = [];
$pmlIds = [];
for ($i = 1; $i <= 28; $i++) {
    $nama = 'Pcl Dummy ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
    $insOrang->execute([':n' => $nama]);
    $s = $pdo->prepare('SELECT id FROM orang WHERE nama=:n');
    $s->execute([':n' => $nama]);
    $pclIds[] = (int) $s->fetchColumn();
}
for ($i = 1; $i <= 15; $i++) {
    $nama = 'Pml Dummy ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
    $insOrang->execute([':n' => $nama]);
    $s = $pdo->prepare('SELECT id FROM orang WHERE nama=:n');
    $s->execute([':n' => $nama]);
    $pmlIds[] = (int) $s->fetchColumn();
}
$pengolahIds = array_map(static fn (int $i): int => $i, range(1, 8)); // 8 pengolah asli
echo 'orang: ', count($pclIds) + count($pmlIds), ' dummy (28 PCL + 15 PML)', PHP_EOL;

// ---------------------------------------------------------------- 2) USERS DEMO
// Password satu untuk semua akun demo: Dummy3509!  (must_reset=0 agar langsung bisa login)
$hash = password_hash('Dummy3509!', PASSWORD_ARGON2ID);
$demo = [
    ['Petugas PCL Demo', 'pcl.demo@bpsjember.go.id', $roleId('PCL'), $pclIds[0]],
    ['Petugas PML Demo', 'pml.demo@bpsjember.go.id', $roleId('PML'), $pmlIds[0]],
    ['Petugas Operator Demo', 'operator.demo@bpsjember.go.id', $roleId('OPERATOR'), null],
    ['Petugas Viewer Demo', 'viewer.demo@bpsjember.go.id', $roleId('VIEWER'), null],
];
$insUser = $pdo->prepare(
    'INSERT INTO users (nama, email, password_hash, role_id, orang_id, is_aktif, must_reset)
     VALUES (:n,:e,:h,:r,:o,1,0)
     ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), role_id=VALUES(role_id),
       orang_id=VALUES(orang_id), is_aktif=1, must_reset=0'
);
foreach ($demo as [$n, $e, $r, $o]) {
    $insUser->execute([':n' => $n, ':e' => $e, ':h' => $hash, ':r' => $r, ':o' => $o]);
}
echo 'users demo: 4 (pcl/pml/operator/viewer, password Dummy3509!)', PHP_EOL;

// ---------------------------------------------------------------- 3) PERIODE TAMBAHAN
$insPeriode = $pdo->prepare(
    "INSERT INTO periode (tahun, jenis, label, tgl_mulai, tgl_selesai, status, catatan)
     VALUES (:t,:j,:l,:m,:s,:st,:c)
     ON DUPLICATE KEY UPDATE label=VALUES(label), status=VALUES(status)"
);
$insPeriode->execute([':t' => 2026, ':j' => 'SUSENAS_S1', ':l' => '2026-S1 Susenas Maret',
    ':m' => '2026-03-01', ':s' => '2026-03-31', ':st' => 'TUTUP',
    ':c' => 'Periode DUMMY (TUTUP): demo histori + rotasi petugas.']);
$insPeriode->execute([':t' => 2026, ':j' => 'SERUTI_Q1', ':l' => '2026-Q1 Seruti Triwulan I',
    ':m' => null, ':s' => null, ':st' => 'DRAFT',
    ':c' => 'Periode DUMMY (DRAFT): demo SLS berulang + penugasan baru.']);
$pid = static function (string $jenis) use ($pdo): int {
    $s = $pdo->prepare("SELECT id FROM periode WHERE tahun=2026 AND jenis='$jenis'");
    $s->execute();
    return (int) $s->fetchColumn();
};
$pS2 = $pid('SUSENAS_S2');   // sudah ada (AKTIF, 28 sampel)
$pS1 = $pid('SUSENAS_S1');   // TUTUP
$pQ1 = $pid('SERUTI_Q1');    // DRAFT
echo "periode: S2=$pS2 AKTIF, S1=$pS1 TUTUP, Q1=$pQ1 DRAFT", PHP_EOL;

// ---------------------------------------------------------------- 4) SAMPEL
$allSls = $pdo->query('SELECT id, jml_rt FROM sls ORDER BY nks')->fetchAll(PDO::FETCH_ASSOC);

// S1 (TUTUP): 28 SLS, hasil pemutakhiran dummy terisi, dok+peta rapi (kecuali 3 yang belum).
$insSampel = $pdo->prepare(
    'INSERT INTO sampel (periode_id, sls_id, target_sampel, muatan_awal, hasil_kk, hasil_rt, dokumen_vsen, peta_ws)
     VALUES (:p,:s,10,:m,:kk,:rt,:d,:w)
     ON DUPLICATE KEY UPDATE muatan_awal=VALUES(muatan_awal)'
);
foreach ($allSls as $i => $r) {
    $muatan = (int) ($r['jml_rt'] ?? 50);
    $insSampel->execute([':p' => $pS1, ':s' => (int) $r['id'], ':m' => $muatan,
        ':kk' => $muatan, ':rt' => max(0, $muatan - 3), ':d' => ($i < 25 ? 1 : 0), ':w' => ($i < 25 ? 1 : 0)]);
}

// Q1 (DRAFT): subset 10 SLS — termasuk 56361 yang juga ada di S2 (demo SLS berulang).
$q1Sls = array_slice($allSls, 0, 9);
$Sls56361 = $pdo->query("SELECT id, jml_rt FROM sls WHERE nks='56361'")->fetch(PDO::FETCH_ASSOC);
if ($Sls56361 !== false) {
    $q1Sls[] = $Sls56361; // nks 56361 = id desa ROWO TENGAH, sudah disampel di S2
}
foreach ($q1Sls as $r) {
    $insSampel->execute([':p' => $pQ1, ':s' => (int) $r['id'], ':m' => (int) ($r['jml_rt'] ?? 50),
        ':kk' => null, ':rt' => null, ':d' => 0, ':w' => 0]);
}

// S2 (AKTIF): perkaya sampel 56361 dgn hasil pemutakhiran asli PDF (100 KK / 97 RT).
$updS2 = $pdo->prepare(
    "UPDATE sampel SET hasil_kk=100, hasil_rt=97, dokumen_vsen=1, peta_ws=1
     WHERE periode_id=:p AND sls_id=(SELECT id FROM sls WHERE nks='56361')"
);
$updS2->execute([':p' => $pS2]);
// Sisanya S2: 14 pertama dokumen+peta lengkap (demo progres bervariasi)
$updS2b = $pdo->prepare(
    'UPDATE sampel SET dokumen_vsen=1, peta_ws=1 WHERE periode_id=:p AND id IN (
       SELECT id FROM (SELECT id FROM sampel WHERE periode_id=:p2 ORDER BY id LIMIT 14) x)'
);
$updS2b->execute([':p' => $pS2, ':p2' => $pS2]);
echo 'sampel: S1=28, Q1=', count($q1Sls), ' (subset, 56361 ikut utk demo berulang)', PHP_EOL;

// ---------------------------------------------------------------- 5) PENUGASAN (K4)
// Pool per periode saling lepas: PCL dummy hanya PCL, PML dummy hanya PML,
// pengolah 1-8 hanya PENGOLAH -> 1 orang 1 peran per periode terpenuhi.
// S2: rotasi 0; S1: shift 5/7/3 (demo petugas beda antar periode); Q1: urut 0..9.
$assign = $pdo->prepare(
    'INSERT INTO penugasan (sampel_id, pcl_id, pml_id, pengolah_id, status)
     VALUES (:sp,:pc,:pm,:po,:st)
     ON DUPLICATE KEY UPDATE pcl_id=VALUES(pcl_id), pml_id=VALUES(pml_id),
       pengolah_id=VALUES(pengolah_id), status=VALUES(status)'
);
$n = 0;
$rows = $pdo->prepare('SELECT id FROM sampel WHERE periode_id=:p ORDER BY id');
$rows->execute([':p' => $pS2]);
foreach ($rows->fetchAll(PDO::FETCH_COLUMN) as $i => $sampelId) {
    $i = (int) $i;
    $assign->execute([':sp' => (int) $sampelId, ':pc' => $pclIds[$i % 28],
        ':pm' => $pmlIds[$i % 15], ':po' => $pengolahIds[$i % 8],
        ':st' => ($i < 8 ? 'SELESAI' : 'AKTIF')]);
    $n++;
}
$rows->execute([':p' => $pS1]);
foreach ($rows->fetchAll(PDO::FETCH_COLUMN) as $i => $sampelId) {
    $i = (int) $i;
    $assign->execute([':sp' => (int) $sampelId, ':pc' => $pclIds[($i + 5) % 28],
        ':pm' => $pmlIds[($i + 7) % 15], ':po' => $pengolahIds[($i + 3) % 8], ':st' => 'SELESAI']);
    $n++;
}
$rows->execute([':p' => $pQ1]);
foreach ($rows->fetchAll(PDO::FETCH_COLUMN) as $i => $sampelId) {
    $assign->execute([':sp' => (int) $sampelId, ':pc' => $pclIds[$i % 28],
        ':pm' => $pmlIds[$i % 15], ':po' => $pengolahIds[$i % 8], ':st' => 'DRAFT']);
    $n++;
}
echo "penugasan: $n baris (S2 28 AKTIF/SELESAI, S1 28 SELESAI, Q1 10 DRAFT)", PHP_EOL;

// ---------------------------------------------------------------- 6) VERIFIKASI
$rek = $pdo->prepare(
    "SELECT pe.label, pe.status, COUNT(DISTINCT sp.id) jml_sampel,
            COUNT(DISTINCT pg.pcl_id) jml_pcl, COUNT(pg.pcl_id)*10 target_total
     FROM periode pe
     LEFT JOIN sampel sp ON sp.periode_id=pe.id
     LEFT JOIN penugasan pg ON pg.sampel_id=sp.id
     GROUP BY pe.id, pe.label, pe.status ORDER BY pe.id"
);
$rek->execute();
foreach ($rek->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo sprintf('  %-28s %-6s sampel=%2s pcl=%2s target=%3s',
        $r['label'], $r['status'], $r['jml_sampel'], $r['jml_pcl'], $r['target_total']), PHP_EOL;
}
// K4 harus 0: orang dengan 2 peran beda dalam 1 periode
$viol = $pdo->query(
    'SELECT COUNT(*) FROM (
       SELECT periode_id, o, COUNT(DISTINCT peran) c FROM (
         SELECT sp.periode_id, pg.pcl_id o, "PCL" peran FROM penugasan pg JOIN sampel sp ON sp.id=pg.sampel_id
         UNION ALL
         SELECT sp.periode_id, pg.pml_id, "PML" FROM penugasan pg JOIN sampel sp ON sp.id=pg.sampel_id
         UNION ALL
         SELECT sp.periode_id, pg.pengolah_id, "PENGOLAH" FROM penugasan pg JOIN sampel sp ON sp.id=pg.sampel_id
       ) t GROUP BY periode_id, o HAVING c>1
     ) v'
)->fetchColumn();
if ((int) $viol > 0) {
    fwrite(STDERR, "VERIFIKASI GAGAL: $viol pelanggaran K4\n");
    exit(1);
}
echo 'VERIFIKASI OK: K4 terpenuhi (0 pelanggaran), SLS 56361 tampil di S2 + Q1', PHP_EOL;
echo "SEED 004 DUMMY SELESAI\n";
