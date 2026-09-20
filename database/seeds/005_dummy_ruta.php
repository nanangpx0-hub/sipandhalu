<?php

declare(strict_types=1);

/**
 * Seed 005 - BARIS RUTA DUMMY untuk uji coba Lembar Kerja Pengolahan.
 * Mengisi 10 baris sampel_ruta per sampel (idempoten: lewati yang sudah ada),
 * lalu mengeset variasi progres sesuai status periode:
 *   TUTUP -> semua ADA/SUDAH + transfer selesai,
 *   AKTIF -> progres bervariasi per sampel (ada selesai, sebagian, baru mulai),
 *   DRAFT -> semua BELUM (fresh).
 * Hanya menyentuh baris yang masih default (belum diutak-atik user).
 * Jalankan: php database/seeds/005_dummy_ruta.php
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

// ---------------------------------------------------------------- 1) PASTIKAN 10 BARIS PER SAMPEL
$baru = (int) $pdo->exec(
    'INSERT IGNORE INTO sampel_ruta (sampel_id, no_urut_ruta)
     SELECT sp.id, n.no
     FROM sampel sp
     CROSS JOIN (SELECT 1 AS no UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
                 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10) n'
);
echo "baris ruta baru: $baru", PHP_EOL;

// Nama pengolah per sampel (untuk ttd dummy)
$pengolahOf = [];
$s = $pdo->query(
    'SELECT pg.sampel_id, o.nama FROM penugasan pg LEFT JOIN orang o ON o.id = pg.pengolah_id'
);
foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $pengolahOf[(int) $r['sampel_id']] = (string) ($r['nama'] ?? 'Tim IPDS');
}

// ---------------------------------------------------------------- 2) VARIASI PROGRES
$upd = $pdo->prepare(
    "UPDATE sampel_ruta sr
     JOIN sampel sp ON sp.id = sr.sampel_id
     SET sr.status_dokumen = :dok, sr.status_selesai = :sel,
         sr.status_transfer_k = :tk, sr.status_transfer_kp = :tkp,
         sr.status_transfer_seruti = :ts,
         sr.catatan_modul = :cm, sr.ket_m_pengolah = :kmp,
         sr.catatan_kp = :ck, sr.ket_kp_pengolah = :kkp,
         sr.tgl_pengiriman = :tgl, sr.ttd_sos = :sos, sr.ttd_ipds = :ipds
     WHERE sr.id = :id
       AND sr.status_dokumen = 'BELUM' AND sr.status_selesai = 'BELUM'
       AND sr.ket_kp_pengolah IS NULL AND sr.ket_m_pengolah IS NULL"
);

$sampels = $pdo->query(
    'SELECT sp.id, sp.periode_id, pe.status, sl.nks
     FROM sampel sp JOIN periode pe ON pe.id = sp.periode_id
     LEFT JOIN sls sl ON sl.id = sp.sls_id ORDER BY sp.id'
)->fetchAll(PDO::FETCH_ASSOC);

$diset = 0;
foreach ($sampels as $sp) {
    $sid = (int) $sp['id'];
    $status = (string) $sp['status'];
    $rows = $pdo->prepare('SELECT id, no_urut_ruta FROM sampel_ruta WHERE sampel_id = :s ORDER BY no_urut_ruta');
    $rows->execute([':s' => $sid]);
    foreach ($rows->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $no = (int) $r['no_urut_ruta'];
        // Pola default: BELUM semua
        $v = ['dok' => 'BELUM', 'sel' => 'BELUM', 'tk' => 0, 'tkp' => 0, 'ts' => 0,
            'cm' => 0, 'kmp' => null, 'ck' => 0, 'kkp' => null,
            'tgl' => null, 'sos' => null, 'ipds' => null];
        if ($status === 'TUTUP') {
            $v = ['dok' => 'ADA', 'sel' => 'SUDAH', 'tk' => 1, 'tkp' => 1, 'ts' => 0,
                'cm' => 0, 'kmp' => null, 'ck' => 0, 'kkp' => null,
                'tgl' => '2026-03-28', 'sos' => 'Tim Sosial', 'ipds' => $pengolahOf[$sid] . ' (Tim IPDS)'];
            if ($no % 4 === 0) {
                $v['ck'] = 1;
                $v['kkp'] = 'Rincian KP SLS ' . ($sp['nks'] ?? '') . ' ruta ' . $no . ' perlu cek ulang kode lapangan';
            }
        } elseif ($status === 'AKTIF') {
            $mode = $sid % 3;
            $ambangAda = [0 => 10, 1 => 6, 2 => 3][$mode]; // variasi: tuntas / setengah / baru mulai
            if ($no <= $ambangAda) {
                $v['dok'] = 'ADA';
                $v['tgl'] = '2026-09-' . str_pad((string) (10 + ($sid + $no) % 15), 2, '0', STR_PAD_LEFT);
                $v['sos'] = 'Tim Sosial';
                $v['ipds'] = $pengolahOf[$sid] . ' (Tim IPDS)';
                if ($no <= 4) {
                    $v['tk'] = 1;
                }
                if ($no <= 2) {
                    $v['tkp'] = 1;
                    $v['sel'] = 'SUDAH';
                }
                if ($no % 4 === 0) {
                    $v['ck'] = 1;
                    $v['kkp'] = 'Catatan KP ruta ' . $no . ': selisih 1 ART, menunggu konfirmasi PML';
                }
                if ($no % 5 === 0) {
                    $v['cm'] = 1;
                    $v['kmp'] = 'Catatan modul ruta ' . $no . ': konsumsi dimutakhirkan';
                }
            }
        }
        // DRAFT: biarkan BELUM semua (fresh)
        $upd->execute([
            ':dok' => $v['dok'], ':sel' => $v['sel'], ':tk' => $v['tk'], ':tkp' => $v['tkp'], ':ts' => $v['ts'],
            ':cm' => $v['cm'], ':kmp' => $v['kmp'], ':ck' => $v['ck'], ':kkp' => $v['kkp'],
            ':tgl' => $v['tgl'], ':sos' => $v['sos'], ':ipds' => $v['ipds'], ':id' => (int) $r['id'],
        ]);
        $diset += $upd->rowCount();
    }
}
echo "baris diberi progres dummy: $diset", PHP_EOL;

// ---------------------------------------------------------------- 3) LAPORAN per pengolah (fokus: anung orang_id=2)
echo "--- rekap ruta per pengolah (periode AKTIF) ---", PHP_EOL;
$rek = $pdo->query(
    "SELECT pg.pengolah_id, o.nama, COUNT(sr.id) total,
            SUM(sr.status_dokumen='ADA') ada, SUM(sr.status_selesai='SUDAH') selesai
     FROM sampel_ruta sr
     JOIN sampel sp ON sp.id = sr.sampel_id
     JOIN periode pe ON pe.id = sp.periode_id AND pe.status = 'AKTIF'
     JOIN penugasan pg ON pg.sampel_id = sp.id
     LEFT JOIN orang o ON o.id = pg.pengolah_id
     GROUP BY pg.pengolah_id ORDER BY pg.pengolah_id"
);
foreach ($rek->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo sprintf('  pengolah=%-2s %-28s total=%-3s ada=%-3s selesai=%-3s',
        $r['pengolah_id'], $r['nama'], $r['total'], $r['ada'], $r['selesai']), PHP_EOL;
}
echo 'TOTAL sampel_ruta: ', $pdo->query('SELECT COUNT(*) FROM sampel_ruta')->fetchColumn(), PHP_EOL;
echo "SEED 005 DUMMY RUTA SELESAI\n";
