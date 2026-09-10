<?php

declare(strict_types=1);

/**
 * Seed 003 - Padan SLS dari PDF resmi BPS: "data/56361_Susenas 2026__final.pdf"
 * (Susenas 2026 VSEN26-DSRT, DAFTAR SAMPEL RUMAH TANGGA SEPTEMBER - RAHASIA).
 *
 * Hasil ekstraksi pdfplumber (Blok I-II):
 *   Kecamatan   : 170 - SUMBERBARU
 *   Desa        : 002 - ROWO TENGAH
 *   Kode SLS/Sub: 004200  -> sls=0042, sub=00
 *   NKS         : 56361
 *   SLS         : RT 001 RW 014 DUSUN SADENGAN
 *   Klasifikasi : 1 (Perkotaan)
 *   Blok II     : Jml Keluarga Hasil Pemutakhiran = 100, Jml Rumah Tangga = 97
 *
 * Catatan muatan: Excel alokasi mencatat 101 (frame awal) -> tetap sebagai
 * sampel.muatan_awal; PDF 100 KK / 97 RT adalah hasil pemutakhiran (jml_kk/jml_rt).
 * kode_full 16 digit mengikuti format BPS: prov2+kab2+kec3+desa3+sls4+sub2.
 * Idempoten. Jalankan: php database/seeds/003_sls_pdf_seed.php
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

const PDF_NKS = '56361';
const PDF_DATA = [
    'kec' => '170',
    'desa' => '002',
    'sls' => '0042',
    'sub' => '00',
    'dusun' => 'SADENGAN',
    'rw' => '014',
    'rt' => '001',
    'klasifikasi' => 1,
    'jml_kk' => 100,
    'jml_rt' => 97,
    'muatan_awal' => 101, // frame awal dari Excel "Alokasi (2).xlsx"
];

// Susun kode_full 16 digit: 35 + 09 + kec3 + desa3 + sls4 + sub2
$kodeFull = '35' . '09' . PDF_DATA['kec'] . PDF_DATA['desa'] . PDF_DATA['sls'] . PDF_DATA['sub'];
if (!preg_match('/^\d{16}$/', $kodeFull)) {
    fwrite(STDERR, "kode_full tidak valid: $kodeFull\n");
    exit(1);
}

// 1) Pastikan desa master ada (idempoten)
$pd = $pdo->prepare('INSERT IGNORE INTO desa (kecamatan_kode, kode, nama) VALUES (:k,:d,:n)');
$pd->execute([':k' => PDF_DATA['kec'], ':d' => PDF_DATA['desa'], ':n' => 'ROWO TENGAH']);

$gd = $pdo->prepare('SELECT id FROM desa WHERE kecamatan_kode=:k AND kode=:d');
$gd->execute([':k' => PDF_DATA['kec'], ':d' => PDF_DATA['desa']]);
$desaId = $gd->fetchColumn();
if ($desaId === false) {
    fwrite(STDERR, 'Desa master tidak ditemukan' . PHP_EOL);
    exit(1);
}
$desaId = (int) $desaId;

// 2) Pastikan SLS ada lalu padankan dengan PDF
$ps = $pdo->prepare('INSERT IGNORE INTO sls (kec, desa, nks, nama_sls, jml_rt) VALUES (:k,:d,:n,:m,:r)');
$ps->execute([':k' => PDF_DATA['kec'], ':d' => PDF_DATA['desa'], ':n' => PDF_NKS, ':m' => 'ROWO TENGAH', ':r' => PDF_DATA['jml_rt']]);

$up = $pdo->prepare(
    'UPDATE sls SET
        desa_id = :desa_id,
        sls = :sls,
        sub = :sub,
        kode_full = :kode_full,
        dusun = :dusun,
        rw = :rw,
        rt = :rt,
        klasifikasi = :klasifikasi,
        jml_kk = :jml_kk,
        jml_rt = :jml_rt
     WHERE nks = :nks'
);
$up->execute([
    ':desa_id' => $desaId,
    ':sls' => PDF_DATA['sls'],
    ':sub' => PDF_DATA['sub'],
    ':kode_full' => $kodeFull,
    ':dusun' => PDF_DATA['dusun'],
    ':rw' => PDF_DATA['rw'],
    ':rt' => PDF_DATA['rt'],
    ':klasifikasi' => PDF_DATA['klasifikasi'],
    ':jml_kk' => PDF_DATA['jml_kk'],
    ':jml_rt' => PDF_DATA['jml_rt'],
    ':nks' => PDF_NKS,
]);
echo "sls NKS ", PDF_NKS, " dipadankan: kode_full=$kodeFull, RT ", PDF_DATA['rt'], "/RW ", PDF_DATA['rw'],
     ' Dusun ', PDF_DATA['dusun'], ', KK=', PDF_DATA['jml_kk'], ', RT=', PDF_DATA['jml_rt'], PHP_EOL;

// 3) Samakan muatan_awal sampel periode berjalan dgn frame awal Excel (idempoten)
$mu = $pdo->prepare(
    'UPDATE sampel SET muatan_awal=:m WHERE sls_id=(SELECT id FROM sls WHERE nks=:nks)'
);
$mu->execute([':m' => PDF_DATA['muatan_awal'], ':nks' => PDF_NKS]);
echo 'sampel muatan_awal = ', PDF_DATA['muatan_awal'], ' (frame Excel), jml pemutakhiran 100 KK / 97 RT', PHP_EOL;

// 4) Verifikasi baca-balik
$q = $pdo->prepare(
    'SELECT nks, kode_full, sls, sub, dusun, rw, rt, klasifikasi, jml_kk, jml_rt, is_aktif
     FROM sls WHERE nks=:nks'
);
$q->execute([':nks' => PDF_NKS]);
$row = $q->fetch(PDO::FETCH_ASSOC);
if ($row === false) {
    fwrite(STDERR, 'VERIFIKASI GAGAL: baris tidak ada' . PHP_EOL);
    exit(1);
}
foreach (['sls' => PDF_DATA['sls'], 'sub' => PDF_DATA['sub'], 'kode_full' => $kodeFull, 'dusun' => PDF_DATA['dusun'],
          'rw' => PDF_DATA['rw'], 'rt' => PDF_DATA['rt'], 'klasifikasi' => (string) PDF_DATA['klasifikasi'],
          'jml_kk' => (string) PDF_DATA['jml_kk'], 'jml_rt' => (string) PDF_DATA['jml_rt']] as $k => $v) {
    if ((string) $row[$k] !== $v) {
        fwrite(STDERR, "VERIFIKASI GAGAL: $k=" . (string) $row[$k] . " != $v" . PHP_EOL);
        exit(1);
    }
}
echo 'VERIFIKASI OK: ', json_encode($row, JSON_UNESCAPED_UNICODE), PHP_EOL;
echo "SEED 003 PDF SELESAI\n";
