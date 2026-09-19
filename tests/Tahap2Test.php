<?php

declare(strict_types=1);

namespace Tests;

use App\Core\KodeBps;
use App\Repositories\AuditRepository;
use App\Repositories\DesaRepository;
use App\Repositories\OrangRepository;
use App\Repositories\PeriodeRepository;
use App\Repositories\SampelRepository;
use App\Repositories\SlsRepository;
use App\Repositories\WilayahRepository;
use App\Services\PenugasanService;
use App\Services\SlsService;
use PDO;
use PHPUnit\Framework\TestCase;

final class Tahap2Test extends TestCase
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
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        foreach (['jadwal_pengawas_pengolahan', 'penugasan', 'sampel', 'periode', 'sls', 'desa', 'kecamatan', 'audit_logs', 'users', 'orang_alias', 'orang'] as $t) {
            $this->pdo->exec("TRUNCATE TABLE $t");
        }
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        $this->pdo->exec("INSERT INTO kecamatan (kode,nama) VALUES ('170','SUMBERBARU'),('250','LEDOKOMBO')");
$this->pdo->exec("INSERT INTO roles (code,label) VALUES ('ADMIN','Administrator') ON DUPLICATE KEY UPDATE label=VALUES(label)");
    }

    public function testKode16PecahRakit(): void
    {
        // kunci diskusi: 35|09|010|001|0001|00, split 4+2
        $p = KodeBps::pecah16('3509010001000100');
        $this->assertSame(['prov' => '35', 'kab' => '09', 'kec' => '010', 'desa' => '001', 'sls' => '0001', 'sub' => '00'], $p);
        $this->assertSame('3509010001000100', KodeBps::rakit16('35', '09', '010', '001', '0001', '00'));
        $this->assertNull(KodeBps::pecah16('56361')); // NKS 5 digit bukan kode 16
        $this->assertSame('00327', KodeBps::pad('327', 5)); // leading zero Excel
        // contoh PDF: 004200 = 0042|00
        $pdf = KodeBps::pecah16('3509170002004200');
        $this->assertSame('0042', $pdf['sls']);
        $this->assertSame('00', $pdf['sub']);
    }

    public function testSlsNksUnikDanKecamatanHarusAda(): void
    {
        $svc = new SlsService(
            $this->pdo,
            new SlsRepository($this->pdo),
            new DesaRepository($this->pdo),
            new WilayahRepository($this->pdo),
            new AuditRepository($this->pdo)
        );
        // kecamatan 999 belum ada -> tolak
        $r = $svc->normalize(['kec' => '999', 'desa' => '001', 'nks' => '56361']);
        $this->assertFalse($r['ok']);
        // ok
        $r = $svc->normalize(['kode_full' => '3509170002004200', 'nks' => '56361', 'nama_sls' => 'Rowo Tengah']);
        $this->assertTrue($r['ok']);
        $this->assertSame('170', $r['data']['kec']);
        $id = (new SlsRepository($this->pdo))->create($r['data']);
        // NKS duplikat -> tolak
        $r2 = $svc->normalize(['kec' => '170', 'desa' => '002', 'nks' => '56361']);
        $this->assertFalse($r2['ok']);
        $this->assertArrayHasKey('nks', $r2['errors']);
        $this->assertGreaterThan(0, $id);
    }

    public function testSatuOrangSatuPeranPerPeriode(): void
    {
        $pdo = $this->pdo;
        $pdo->exec("INSERT INTO orang (nama) VALUES ('PCL A'),('PML B'),('Olah C'),('Rangkap D')");
        $a = 1;
        $b = 2;
        $c = 3;
        $d = 4; // akan coba rangkap PCL+PML
        $pdo->exec("INSERT INTO periode (tahun,jenis,label,status) VALUES (2026,'SUSENAS_S2','2026-S2','AKTIF')");
        $per = 1;
        $pdo->exec("INSERT INTO sls (kec,desa,nks,nama_sls) VALUES ('170','002','56361','S1'),('170','002','56326','S2')");
        $pdo->exec("INSERT INTO sampel (periode_id,sls_id,target_sampel) VALUES ($per,1,10),($per,2,10)");
        $svc = new PenugasanService($pdo, new SampelRepository($pdo), new AuditRepository($pdo));
        // sampel1: A=PCL, B=PML, C=olah -> ok
        $this->assertTrue($svc->assign($per, 1, $a, $b, $c, null, '127.0.0.1', 'phpunit')['ok']);
        // sampel2: D=PCL, B=PML, C=olah -> ok (B tetap PML, C tetap olah, D baru PCL)
        $this->assertTrue($svc->assign($per, 2, $d, $b, $c, null, '127.0.0.1', 'phpunit')['ok']);
        // coba rangkap: D sudah PCL di sampel2, jadikan PML di sampel1 -> tolak K4
        $r = $svc->assign($per, 1, $a, $d, $c, null, '127.0.0.1', 'phpunit');
        $this->assertFalse($r['ok']);
        // 3 peran sama orang -> tolak
        $r = $svc->assign($per, 1, $a, $a, $c, null, '127.0.0.1', 'phpunit');
        $this->assertFalse($r['ok']);
    }

    public function testRingkasan28x10(): void
    {
        $pdo = $this->pdo;
        $pdo->exec("INSERT INTO periode (tahun,jenis,label,status) VALUES (2026,'SUSENAS_S2','2026-S2','AKTIF')");
        for ($i = 1; $i <= 28; $i++) {
            $nks = str_pad((string) (50000 + $i), 5, '0', STR_PAD_LEFT);
            $pdo->exec("INSERT INTO sls (kec,desa,nks) VALUES ('170','002','$nks')");
            $sid = (int) $pdo->lastInsertId();
            $pdo->exec("INSERT INTO sampel (periode_id,sls_id,target_sampel,muatan_awal) VALUES (1,$sid,10,50)");
        }
        $ring = (new PeriodeRepository($pdo))->ringkasan(1);
        $this->assertSame(28, (int) $ring['n_sls']);
        $this->assertSame(280, (int) $ring['target']); // 28 SLS x 10 KK/RT
    }
}
