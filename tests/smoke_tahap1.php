<?php

declare(strict_types=1);

// Smoke test tahap 1: bootstrap App tanpa web server (CLI).
// Jalankan: php tests/smoke_tahap1.php

$base = dirname(__DIR__);
require $base . '/vendor/autoload.php';

use App\Core\Config;
use App\Core\Database;
use App\Core\KodeBps;
use App\Repositories\OrangRepository;
use App\Repositories\UserRepository;

Config::loadEnv($base);
$_ENV['LOG_PATH'] = $base . '/storage/logs/app.log';

$checks = [];
$ok = function (string $name, callable $fn) use (&$checks): void {
    try {
        $fn();
        $checks[] = "OK   $name";
    } catch (Throwable $e) {
        $checks[] = 'FAIL ' . $name . ' :: ' . $e->getMessage();
    }
};

$ok('config database.php terbaca', function () use ($base): void {
    $c = require $base . '/config/database.php';
    if (($c['name'] ?? '') !== 'sipandhalu') {
        throw new RuntimeException('nama DB bukan sipandhalu');
    }
});

$ok('koneksi PDO sipandhalu', function (): void {
    Database::connection()->query('SELECT 1');
});

$ok('seed baseline minimal: >=8 orang, >=9 users, >=5 alias (004 dummy boleh menambah)', function (): void {
    $pdo = Database::connection();
    $o = (int) $pdo->query('SELECT COUNT(*) FROM orang')->fetchColumn();
    $u = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $a = (int) $pdo->query('SELECT COUNT(*) FROM orang_alias')->fetchColumn();
    if ($o < 8 || $u < 9 || $a < 5) {
        throw new RuntimeException("orang=$o users=$u alias=$a");
    }
});

$ok('password ter-hash Argon2id + must_reset', function (): void {
    $pdo = Database::connection();
    $row = $pdo->query("SELECT password_hash, must_reset FROM users WHERE email='aminatuss182002@gmail.com'")->fetch();
    if (!str_starts_with($row['password_hash'], '$argon2id$')) {
        throw new RuntimeException('bukan argon2id');
    }
    if ((int) $row['must_reset'] !== 1) {
        throw new RuntimeException('must_reset bukan 1');
    }
    if (!password_verify('Jember3509', $row['password_hash'])) {
        throw new RuntimeException('verify gagal');
    }
});

$ok('resolveAlias tahan typo (anung anindhita p)', function (): void {
    $r = (new OrangRepository(Database::connection()))->resolveAlias('Anung Anindhita P');
    if ($r === null || $r['nama'] !== 'Anung Anindhita Pratiwi') {
        throw new RuntimeException('alias tidak resolve');
    }
});

$ok('rekap CTE+window user per peran jalan', function (): void {
    $rows = (new UserRepository(Database::connection()))->countByRole();
    if ($rows === []) {
        throw new RuntimeException('rekap kosong');
    }
});

$ok('semua view bisa di-parse PHP', function () use ($base): void {
    $views = [
        'auth/login.phtml', 'auth/password.phtml', 'dashboard/index.phtml',
        'orang/index.phtml', 'orang/form.phtml', 'orang/show.phtml',
        'users/index.phtml', 'users/form.phtml',
        'sls/index.phtml', 'sls/form.phtml',
        'periode/index.phtml', 'periode/form.phtml', 'periode/show.phtml',
        'errors/404.phtml', 'errors/403.phtml', 'errors/419.phtml', 'errors/500.phtml',
    ];
    foreach ($views as $v) {
        $out = [];
        $code = 0;
        exec('php -l ' . escapeshellarg($base . '/app/Views/' . $v) . ' 2>&1', $out, $code);
        if ($code !== 0) {
            throw new RuntimeException($v . ': ' . implode(' ', $out));
        }
    }
});

$ok('tabel tahap 2 eksist (@migrate --fresh done)', function (): void {
    $pdo = Database::connection();
    foreach (['kecamatan', 'desa', 'sls', 'periode', 'sampel', 'penugasan'] as $t) {
        $n = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='$t'")->fetchColumn();
        if ($n !== 1) {
            throw new RuntimeException('tabel ' . $t . ' tidak ada');
        }
    }
});

$ok('KodeBps peca 16 digit 4+2 (PDF 004200 = 0042|00)', function (): void {
    $p = KodeBps::pecah16('3509170002004200');
    if (($p['sls'] ?? '') !== '0042' || ($p['sub'] ?? '') !== '00') {
        throw new RuntimeException('pecah16 gagal: ' . json_encode($p));
    }
    if (KodeBps::pad('327', 5) !== '00327') {
        throw new RuntimeException('pad leading zero gagal');
    }
});

$ok('SlsService normalisi kode 16 (kec 170 real, nks unik)', function (): void {
    $pdo = Database::connection();
    $svc = new \App\Services\SlsService(
        $pdo,
        new \App\Repositories\SlsRepository($pdo),
        new \App\Repositories\DesaRepository($pdo),
        new \App\Repositories\WilayahRepository($pdo),
        new \App\Repositories\AuditRepository($pdo)
    );
    $r = $svc->normalize(['kode_full' => '3509170001004200', 'nks' => '99998']);
    if (!$r['ok']) {
        throw new RuntimeException('kode 16 reject: ' . json_encode($r['errors']));
    }
    if (($r['data']['kec'] ?? '') !== '170' || ($r['data']['sls'] ?? '') !== '0042') {
        throw new RuntimeException('pecah kode 16 wrong: ' . json_encode($r['data']));
    }
});

foreach ($checks as $c) {
    echo $c, PHP_EOL;
}
$fail = array_filter($checks, fn ($c) => str_starts_with($c, 'FAIL'));
exit($fail === [] ? 0 : 1);
