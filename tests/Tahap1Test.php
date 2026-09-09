<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Validator;
use App\Repositories\AuditRepository;
use App\Repositories\OrangRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\OrangService;
use App\Services\UserService;
use PDO;
use PHPUnit\Framework\TestCase;

final class Tahap1Test extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $cfg = require dirname(__DIR__) . '/config/database.php';
        $cfg['name'] = 'sipandhalu_test';
        $this->pdo = new PDO(
            sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']),
            $cfg['user'],
            $cfg['pass'],
            $cfg['options']
        );
        // reset tabel test
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        foreach (['audit_logs', 'users', 'orang_alias', 'orang', 'roles'] as $t) {
            $this->pdo->exec("TRUNCATE TABLE $t");
        }
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        $this->pdo->exec(
            "INSERT INTO roles (code,label) VALUES ('ADMIN','Administrator'),('PENGOLAH','Pengolah'),('PCL','Pencacah Lapangan'),('PML','Pengawas Lapangan')"
        );
    }

    public function testValidatorNama(): void
    {
        $this->assertNotEmpty(Validator::orang(['nama' => '']));
        $this->assertSame([], Validator::orang(['nama' => 'Aminatus Sholeha']));
        $this->assertSame('Junaidi Ari Siswanto', Validator::canonicalNama('  junaidi  ari   SISWANTO '));
        $this->assertSame('muhammad fahrul rozy', Validator::normalized('Muhammad Fahrul Rozy '));
    }

    public function testOrangCreateDanDedupe(): void
    {
        $svc = new OrangService($this->pdo, new OrangRepository($this->pdo), new AuditRepository($this->pdo));
        $r1 = $svc->create(['nama' => 'Junaidi Ari Siswanto'], null, '127.0.0.1', 'phpunit');
        $this->assertTrue($r1['ok']);
        $r2 = $svc->create(['nama' => '  junaidi ari siswanto '], null, '127.0.0.1', 'phpunit');
        $this->assertFalse($r2['ok']); // dedup case-insensitive + trim
        // alias: Rozy vs Rozi
        $this->assertTrue($svc->addAlias($r1['id'], 'Junaidi', null, '127.0.0.1', 'phpunit')['ok']);
        $repo = new OrangRepository($this->pdo);
        $this->assertSame('Junaidi Ari Siswanto', $repo->resolveAlias('junaidi')['nama']);
    }

    public function testUserCreateLoginReset(): void
    {
        $users = new UserRepository($this->pdo);
        $svc = new UserService($this->pdo, $users, new AuditRepository($this->pdo));
        $adminRole = (int) $this->pdo->query("SELECT id FROM roles WHERE code='ADMIN'")->fetchColumn();
        $r = $svc->create([
            'nama' => 'Aminatus Sholeha', 'email' => 'Aminatus@Example.com',
            'password' => 'Rahasia123', 'role_id' => $adminRole,
        ], null, '127.0.0.1', 'phpunit');
        $this->assertTrue($r['ok']);
        // email disimpan lowercase + unik
        $dupe = $svc->create([
            'nama' => 'X', 'email' => 'aminatus@example.com',
            'password' => 'Rahasia123', 'role_id' => $adminRole,
        ], null, '127.0.0.1', 'phpunit');
        $this->assertFalse($dupe['ok']);

        $auth = new AuthService($this->pdo, $users, new AuditRepository($this->pdo));
        $this->assertTrue($auth->attempt('aminatus@example.com', 'Rahasia123', '127.0.0.1', 'phpunit')['ok']);
        $this->assertFalse($auth->attempt('aminatus@example.com', 'salah', '127.0.0.1', 'phpunit')['ok']);
        // hash Argon2id, bukan plaintext Jember3509
        $row = $users->findByEmail('aminatus@example.com');
        $this->assertStringStartsWith('$argon2id$', $row['password_hash']);

        $plain = $svc->resetPassword((int) $row['id'], null, '127.0.0.1', 'phpunit');
        $this->assertNotEmpty($plain);
        $this->assertTrue($auth->attempt('aminatus@example.com', $plain, '127.0.0.1', 'phpunit')['ok']);
    }

    public function testTidakBisaNonaktifDiriSendiri(): void
    {
        $users = new UserRepository($this->pdo);
        $svc = new UserService($this->pdo, $users, new AuditRepository($this->pdo));
        $adminRole = (int) $this->pdo->query("SELECT id FROM roles WHERE code='ADMIN'")->fetchColumn();
        $r = $svc->create([
            'nama' => 'Admin', 'email' => 'a@x.id', 'password' => 'Rahasia123', 'role_id' => $adminRole,
        ], null, '127.0.0.1', 'phpunit');
        $this->assertFalse($svc->setAktif($r['id'], false, null, '127.0.0.1', 'phpunit', $r['id']));
    }
}
