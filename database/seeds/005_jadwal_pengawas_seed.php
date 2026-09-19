<?php

declare(strict_types=1);

/**
 * Seed 005 - Jadwal Pengawas Pengolahan Susenas S2 2026.
 * - Pastikan 5 pengawas di `orang` dengan role PENGAWAS_OLAH
 *   (Wahyu Wijayanti update dari ID 141 bila ada).
 * - Isi 26 baris jadwal 2026-09-15 s.d. 2026-10-10 untuk periode SUSENAS_S2
 *   (LIBUR: 19, 20, 26, 27 September 2026).
 * Idempoten. Jalankan: php database/seeds/005_jadwal_pengawas_seed.php
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
        // fallback: buat bila belum ada (mis. DB lama sebelum migrasi 005)
        $pdo->prepare('INSERT INTO roles (code, label) VALUES (:c,:l)')->execute([':c' => $code, ':l' => $code]);
        return (int) $pdo->lastInsertId();
    }
    return (int) $id;
};
$pengawasRole = $roleId('PENGAWAS_OLAH');
echo "role PENGAWAS_OLAH = $pengawasRole" . PHP_EOL;

// ---------------------------------------------------------------- 1) MASTER 5 PENGAWAS
$names = [
    'Arumita Hertriesa',
    'Wahyu Wijayanti',
    'Nanang Pamungkas',
    'Qudrat Jufrian Bharata',
    'Silvie Kristya Ardearista',
];
$insOrang = $pdo->prepare('INSERT IGNORE INTO orang (nama) VALUES (:n)');
$updRole = $pdo->prepare('UPDATE orang SET role_id=:r, is_aktif=1 WHERE id=:id');
$byName = $pdo->prepare('SELECT id, role_id FROM orang WHERE nama_normalized=LOWER(TRIM(:n)) LIMIT 1');
$orangIds = [];
foreach ($names as $nama) {
    $insOrang->execute([':n' => $nama]);
    $byName->execute([':n' => $nama]);
    $row = $byName->fetch(PDO::FETCH_ASSOC);
    if ($row === false) {
        fwrite(STDERR, "Gagal resolve orang $nama\n");
        exit(1);
    }
    $oid = (int) $row['id'];
    if ((int) ($row['role_id'] ?? 0) !== $pengawasRole) {
        $updRole->execute([':r' => $pengawasRole, ':id' => $oid]);
        echo "role+: $nama (id $oid) -> PENGAWAS_OLAH" . PHP_EOL;
    } else {
        echo "role ok: $nama (id $oid)" . PHP_EOL;
    }
    $orangIds[$nama] = $oid;
}
// Pastikan ID 141 (Wahyu lama, role PML) ikut jadi PENGAWAS_OLAH + alias kanonik.
$cek141 = $pdo->prepare('SELECT id, nama, role_id FROM orang WHERE id=141 LIMIT 1');
$cek141->execute();
$r141 = $cek141->fetch(PDO::FETCH_ASSOC);
if ($r141 !== false) {
    if ((int) $r141['id'] !== $orangIds['Wahyu Wijayanti']) {
        // ID 141 adalah baris berbeda (duplikat nama?) — sinkronkan ke kanonik via alias.
        $norm = mb_strtolower(trim((string) $r141['nama']), 'UTF-8');
        try {
            $pdo->prepare('INSERT IGNORE INTO orang_alias (orang_id, alias_normalized) VALUES (:o,:a)')
                ->execute([':o' => $orangIds['Wahyu Wijayanti'], ':a' => $norm]);
            echo "alias+: ID 141 '{$r141['nama']}' -> kanonik Wahyu Wijayanti (id {$orangIds['Wahyu Wijayanti']})" . PHP_EOL;
        } catch (PDOException $e) {
            if (!str_contains($e->getMessage(), 'Duplicate')) {
                throw $e;
            }
        }
    }
    if ((int) ($r141['role_id'] ?? 0) !== $pengawasRole && (int) $r141['id'] === $orangIds['Wahyu Wijayanti']) {
        $updRole->execute([':r' => $pengawasRole, ':id' => (int) $r141['id']]);
        echo "role+: ID 141 -> PENGAWAS_OLAH" . PHP_EOL;
    }
}

// ---------------------------------------------------------------- 2) PERIODE S2 2026
$s = $pdo->prepare("SELECT id FROM periode WHERE tahun=2026 AND jenis='SUSENAS_S2' LIMIT 1");
$s->execute();
$periodeId = (int) $s->fetchColumn();
if ($periodeId <= 0) {
    fwrite(STDERR, "Periode 2026 SUSENAS_S2 tidak ada. Jalankan 002_wilayah_seed dulu.\n");
    exit(1);
}
echo "periode S2 = $periodeId" . PHP_EOL;

// ---------------------------------------------------------------- 3) JADWAL 26 HARI
// Rotasi: Arumita, Wahyu, Nanang, Qudrat, Silvie (berulang). LIBUR: 19,20,26,27 Sep.
$libur = ['2026-09-19' => 'Libur akhir pekan', '2026-09-20' => 'Libur akhir pekan', '2026-09-26' => 'Libur akhir pekan', '2026-09-27' => 'Libur akhir pekan'];
$rotasi = ['Arumita Hertriesa', 'Wahyu Wijayanti', 'Nanang Pamungkas', 'Qudrat Jufrian Bharata', 'Silvie Kristya Ardearista'];
$hariMap = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
$ins = $pdo->prepare(
    'INSERT INTO jadwal_pengawas_pengolahan (periode_id, tanggal, orang_id, nama_pengawas, hari, status, keterangan)
     VALUES (:p,:t,:o,:n,:h,:s,:k)
     ON DUPLICATE KEY UPDATE orang_id=VALUES(orang_id), nama_pengawas=VALUES(nama_pengawas),
       hari=VALUES(hari), status=VALUES(status), keterangan=VALUES(keterangan)'
);
$d = new DateTimeImmutable('2026-09-15');
$end = new DateTimeImmutable('2026-10-10');
$idx = 0;
$n = 0;
while ($d <= $end) {
    $tgl = $d->format('Y-m-d');
    $hari = $hariMap[(int) $d->format('w')];
    if (isset($libur[$tgl])) {
        $ins->execute([':p' => $periodeId, ':t' => $tgl, ':o' => null, ':n' => '-', ':h' => $hari, ':s' => 'LIBUR', ':k' => $libur[$tgl]]);
    } else {
        $nama = $rotasi[$idx % count($rotasi)];
        $idx++;
        $ins->execute([':p' => $periodeId, ':t' => $tgl, ':o' => $orangIds[$nama], ':n' => $nama, ':h' => $hari, ':s' => 'TUGAS', ':k' => 'Piket pengolahan Susenas S2 2026']);
    }
    $n++;
    $d = $d->modify('+1 day');
}
echo "jadwal: $n baris (periode $periodeId, 15 Sep - 10 Okt 2026)" . PHP_EOL;

// ---------------------------------------------------------------- 4) VERIFIKASI
$c = (int) $pdo->query("SELECT COUNT(*) FROM jadwal_pengawas_pengolahan WHERE periode_id=$periodeId")->fetchColumn();
$l = (int) $pdo->query("SELECT COUNT(*) FROM jadwal_pengawas_pengolahan WHERE periode_id=$periodeId AND status='LIBUR'")->fetchColumn();
$t = (int) $pdo->query("SELECT COUNT(*) FROM jadwal_pengawas_pengolahan WHERE periode_id=$periodeId AND status='TUGAS'")->fetchColumn();
echo "verifikasi: total=$c tugas=$t libur=$l" . PHP_EOL;
if ($c !== 26 || $l !== 4 || $t !== 22) {
    fwrite(STDERR, "VERIFIKASI GAGAL: harus 26/22/4\n");
    exit(1);
}
echo "SEED 005 JADWAL SELESAI" . PHP_EOL;
