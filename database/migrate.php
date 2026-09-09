<?php

declare(strict_types=1);

// Migrasi idempoten SIPANDHALU: jalankan semua SQL tahap 1+2 ke DB dev dan test.
// Jalankan: php database/migrate.php [--fresh]
// --fresh: hapus semua tabel tahap 1+2 lalu buat ulang (HATI-HATI: data hilang).

$base = dirname(__DIR__);
require $base . '/vendor/autoload.php';

use App\Core\Config;

Config::loadEnv($base);
$cfg = require $base . '/config/database.php';
$fresh = in_array('--fresh', $argv ?? [], true);

$connect = function (string $db) use ($cfg): PDO {
    return new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $cfg['host'], $cfg['port'], $db, $cfg['charset']),
        $cfg['user'],
        $cfg['pass'],
        $cfg['options']
    );
};

/** Pecah file SQL menjadi statement (abaikan comment -- dan baris kosong). */
$split = function (string $sql): array {
    $lines = explode("\n", str_replace("\r\n", "\n", $sql));
    $clean = [];
    foreach ($lines as $ln) {
        $t = ltrim($ln);
        if ($t === '' || str_starts_with($t, '--')) {
            continue;
        }
        $clean[] = $ln;
    }
    $parts = array_filter(array_map('trim', explode(';', implode("\n", $clean))));
    return array_values($parts);
};

foreach (['sipandhalu', 'sipandhalu_test'] as $db) {
    $pdo = $connect($db);
    echo "== $db ==\n";
    if ($fresh) {
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        foreach (['penugasan', 'sampel', 'periode', 'sls', 'desa', 'kecamatan', 'audit_logs', 'users', 'orang_alias', 'orang', 'roles'] as $t) {
            $pdo->exec("DROP TABLE IF EXISTS `$t`");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        echo "fresh: semua tabel dihapus\n";
    }
    // urutan penting: kecamatan (002a hanya tabel kecamatan) -> wilayah final -> periode
    $files = ['database/schema.sql', 'database/migrations/002a_wilayah.sql', 'database/migrations/002d_wilayah_final.sql', 'database/migrations/002b_periode.sql'];
    foreach ($files as $f) {
        $sql = file_get_contents($base . '/' . $f);
        if ($sql === false) {
            echo "  FAIL baca $f\n";
            exit(1);
        }
        // hapus perintah USE/DROP dari file final agar idempoten (sudah ditangani --fresh)
        $stmts = $split($sql);
        $n = 0;
        foreach ($stmts as $s) {
            if (preg_match('/^(USE|DROP TABLE)/i', ltrim($s))) {
                continue;
            }
            try {
                $pdo->exec($s);
                $n++;
            } catch (PDOException $e) {
                // CREATE IF NOT EXISTS + duplikat constraint diabaikan agar rerun aman
                $msg = $e->getMessage();
                if (str_contains($msg, 'Duplicate') || str_contains($msg, 'already exists')) {
                    continue;
                }
                echo "  FAIL $f :: $msg\n  SQL: " . substr($s, 0, 200) . "\n";
                exit(1);
            }
        }
        echo "  ok $f ($n stmt)\n";
    }
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo '  tables: ' . implode(',', $tables) . "\n";
}
echo "MIGRATE SELESAI\n";
