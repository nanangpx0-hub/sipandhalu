<?php

declare(strict_types=1);

/**
 * Seed tahap 1 SIPANDHALU: roles + 8 orang pengolah (dari sheet Rincian) + alias + 8 users + admin.
 * Password bawaan Excel 'Jember3509' TIDAK disimpan plaintext: di-hash Argon2id + must_reset=1.
 * Kolom H Rincian diabaikan sesuai kesepakatan K3.
 * Jalankan: php database/seeds/seed_tahap1.php
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

$roles = [
    ['ADMIN', 'Administrator'],
    ['OPERATOR', 'Operator Pengolahan'],
    ['PML', 'Pengawas Lapangan'],
    ['PCL', 'Pencacah Lapangan'],
    ['PENGOLAH', 'Pengolah'],
    ['VIEWER', 'Viewer'],
];
$stmt = $pdo->prepare('INSERT INTO roles (code, label) VALUES (:c,:l) ON DUPLICATE KEY UPDATE label=VALUES(label)');
foreach ($roles as [$c, $l]) {
    $stmt->execute([':c' => $c, ':l' => $l]);
}
$roleId = function (string $code) use ($pdo): int {
    $s = $pdo->prepare('SELECT id FROM roles WHERE code=:c LIMIT 1');
    $s->execute([':c' => $code]);
    return (int) $s->fetchColumn();
};

// 8 pengolah dari sheet Rincian (nama kanonik Title Case + TRIM).
// Alias = varian tulis di Daftar alokasi/Sheet1 (6 mismatch): disimpan agar import tahan typo.
$pengolah = [
    ['nama' => 'Aminatus Sholeha', 'email' => 'aminatuss182002@gmail.com', 'alias' => []],
    ['nama' => 'Anung Anindhita Pratiwi', 'email' => 'anunganindhitap@gmail.com', 'alias' => ['anung anindhita p']],
    ['nama' => 'Putri Salsabhila Fahira', 'email' => 'putrisalsabhilafahira10@gmail.com', 'alias' => ['putri salsabhila fahira']],
    ['nama' => 'Iffa Dzakiyya', 'email' => 'bpsbpsiffa36@gmail.com', 'alias' => ['iffa dzakiyya khairunnisa']],
    ['nama' => 'Prasistiwi Andrianingtyas', 'email' => 'prasistiwi@gmail.com', 'alias' => ['prasistiwi']],
    ['nama' => 'Laviana Ika Putrisari', 'email' => 'lavianaikarumby@gmail.com', 'alias' => []],
    ['nama' => 'Nur Ida Suryandari', 'email' => 'nidasuryandari@gmail.com', 'alias' => []],
    ['nama' => 'Astri Widarianti', 'email' => 'a.widarianti@gmail.com', 'alias' => ['astri widarianti']],
];

$hash = password_hash('Jember3509', PASSWORD_ARGON2ID);
if ($hash === false) {
    fwrite(STDERR, "Gagal hash password\n");
    exit(1);
}
$pengolahRole = $roleId('PENGOLAH');

$pdo->beginTransaction();
try {
    // admin default
    $adminRole = $roleId('ADMIN');
    $s = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email=:e');
    $s->execute([':e' => 'admin@bpsjember.go.id']);
    if ((int) $s->fetchColumn() === 0) {
        $ah = password_hash('Admin3509!', PASSWORD_ARGON2ID);
        $pdo->prepare(
            'INSERT INTO users (orang_id, nama, email, password_hash, role_id, is_aktif, must_reset)
             VALUES (NULL,:n,:e,:h,:r,1,1)'
        )->execute([':n' => 'Administrator', ':e' => 'admin@bpsjember.go.id', ':h' => $ah, ':r' => $adminRole]);
        echo "admin dibuat: admin@bpsjember.go.id / Admin3509! (wajib ganti)\n";
    } else {
        echo "admin sudah ada, dilewati\n";
    }

    foreach ($pengolah as $p) {
        // orang
        $s = $pdo->prepare('SELECT id FROM orang WHERE nama_normalized=LOWER(TRIM(:n)) LIMIT 1');
        $s->execute([':n' => $p['nama']]);
        $orangId = $s->fetchColumn();
        if ($orangId === false) {
            $pdo->prepare('INSERT INTO orang (nama, email, is_aktif) VALUES (:n,:e,1)')
                ->execute([':n' => $p['nama'], ':e' => $p['email']]);
            $orangId = $pdo->lastInsertId();
            echo "orang+: {$p['nama']} (id $orangId)\n";
        } else {
            echo "orang sudah ada: {$p['nama']} (id $orangId)\n";
        }
        foreach ($p['alias'] as $a) {
            $norm = mb_strtolower(trim($a), 'UTF-8');
            try {
                $pdo->prepare('INSERT INTO orang_alias (orang_id, alias_normalized) VALUES (:o,:a)')
                    ->execute([':o' => $orangId, ':a' => $norm]);
                echo "  alias+: $norm\n";
            } catch (PDOException $e) {
                if (!str_contains($e->getMessage(), 'Duplicate')) {
                    throw $e;
                }
                echo "  alias sudah ada: $norm\n";
            }
        }
        // users (link orang)
        $s = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email=:e');
        $s->execute([':e' => mb_strtolower($p['email'], 'UTF-8')]);
        if ((int) $s->fetchColumn() === 0) {
            $pdo->prepare(
                'INSERT INTO users (orang_id, nama, email, password_hash, role_id, is_aktif, must_reset)
                 VALUES (:o,:n,:e,:h,:r,1,1)'
            )->execute([':o' => $orangId, ':n' => $p['nama'], ':e' => mb_strtolower($p['email'], 'UTF-8'), ':h' => $hash, ':r' => $pengolahRole]);
            echo "user+: {$p['email']} (hash Jember3509 + must_reset)\n";
        } else {
            echo "user sudah ada: {$p['email']}\n";
        }
    }
    $pdo->commit();
    echo "SEED TAHAP 1 SELESAI\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'Gagal: ' . $e->getMessage() . "\n");
    exit(1);
}
