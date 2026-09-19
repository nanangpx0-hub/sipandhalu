<?php

declare(strict_types=1);

namespace Tests;

use App\Repositories\AuditRepository;
use App\Repositories\DokumenRepository;
use App\Repositories\PeriodeRepository;
use App\Repositories\SampelRepository;
use App\Services\DokumenService;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DokumenTest extends TestCase
{
    private PDO $pdo;
    private SampelRepository $sampelRepo;
    private DokumenRepository $dokumenRepo;
    private DokumenService $svc;

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
        foreach (['jadwal_pengawas_pengolahan', 'peminjaman_dokumen', 'penugasan', 'sampel', 'periode', 'sls', 'desa', 'kecamatan', 'audit_logs', 'users', 'orang_alias', 'orang', 'roles'] as $t) {
            $this->pdo->exec("TRUNCATE TABLE $t");
        }
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=1');

        $this->pdo->exec("INSERT INTO roles (id,code,label) VALUES (1,'ADMIN','Admin'),(2,'OPERATOR','Operator')");
        $this->pdo->exec("INSERT INTO users (id,email,password_hash,nama,role_id) VALUES (1,'op@bps.go.id','hash','Operator Test',2)");
        $this->pdo->exec("INSERT INTO kecamatan (kode,nama) VALUES ('170','SUMBERBARU')");
        $this->pdo->exec("INSERT INTO desa (id,kecamatan_kode,kode,nama) VALUES (1,'170','001','YOSORATI')");
        $this->pdo->exec("INSERT INTO sls (id,desa_id,kec,desa,sls,sub,kode_full,nks,nama_sls) VALUES 
            (1,1,'170','001','0001','00','3509170001000100','56361','RT 01 RW 01'),
            (2,1,'170','001','0002','01','3509170001000201','56362','RT 02 Sub 01')");

        $this->pdo->exec("INSERT INTO orang (id,nama) VALUES (10,'Pencacah Satu'),(20,'Pengawas Satu'),(30,'Pengolah Satu')");

        $this->sampelRepo = new SampelRepository($this->pdo);
        $this->dokumenRepo = new DokumenRepository($this->pdo);
        $this->svc = new DokumenService($this->pdo, $this->sampelRepo, $this->dokumenRepo, new AuditRepository($this->pdo));
    }

    private function createPeriodeAktif(): int
    {
        $pRepo = new PeriodeRepository($this->pdo);
        $id = $pRepo->create([
            'tahun' => 2026,
            'jenis' => 'SERUTI_Q1',
            'label' => 'Seruti 2026 Q1 Test',
            'status' => 'DRAFT',
            'tgl_mulai' => '2026-01-01',
            'tgl_selesai' => '2026-03-31',
            'catatan' => 'Test',
        ]);
        $pRepo->setStatus($id, 'AKTIF');
        return $id;
    }

    public function testPenerimaanSatuanParsialDanSekaligus(): void
    {
        $pId = $this->createPeriodeAktif();
        $sId = $this->sampelRepo->add($pId, 1, 10, 100);
        $this->sampelRepo->assign($sId, 10, 20, 30);

        // 1. Terima Pemutakhiran saja terlebih dahulu
        $this->svc->terimaSatuan(
            $sId,
            [
                'terima_pemutakhiran' => 1,
                'penyerah_id' => 20, // Diserahkan oleh PML
                'waktu_terima' => '2026-03-10 10:00:00',
                'hasil_kk' => 95,
                'hasil_rt' => 4,
                'catatan' => 'Pemutakhiran diserahkan lebih dulu',
            ],
            1
        );

        $row = $this->sampelRepo->find($sId);
        $this->assertSame('DITERIMA', $row['dok_pemutakhiran_status']);
        $this->assertSame(1, (int) $row['dokumen_vsen']);
        $this->assertSame('BELUM', $row['peta_status']);
        $this->assertSame(0, (int) $row['peta_ws']);
        $this->assertSame(95, (int) $row['hasil_kk']);
        $this->assertSame(4, (int) $row['hasil_rt']);

        // 2. Terima Peta susulan
        $this->svc->terimaSatuan(
            $sId,
            [
                'terima_peta' => 1,
                'penyerah_id' => 20,
                'waktu_terima' => '2026-03-11 14:00:00',
            ],
            1
        );

        $row = $this->sampelRepo->find($sId);
        $this->assertSame('DITERIMA', $row['dok_pemutakhiran_status']);
        $this->assertSame('DITERIMA', $row['peta_status']);
        $this->assertSame(1, (int) $row['dokumen_vsen']);
        $this->assertSame(1, (int) $row['peta_ws']);
    }

    public function testPenerimaanKolektifPerPml(): void
    {
        $pId = $this->createPeriodeAktif();
        $sId1 = $this->sampelRepo->add($pId, 1, 10, 100);
        $sId2 = $this->sampelRepo->add($pId, 2, 10, 80);

        $this->sampelRepo->assign($sId1, 10, 20, 30);
        $this->sampelRepo->assign($sId2, 10, 20, 30);

        $count = $this->svc->terimaKolektif(
            $pId,
            20,
            [
                [
                    'sampel_id' => $sId1,
                    'pemutakhiran' => true,
                    'peta' => true,
                    'hasil_kk' => 100,
                    'hasil_rt' => 4,
                ],
                [
                    'sampel_id' => $sId2,
                    'pemutakhiran' => true,
                    'peta' => true,
                    'hasil_kk' => 85,
                    'hasil_rt' => 3,
                ],
            ],
            '2026-03-12 11:30:00',
            'Serah terima serentak dari PML Pak Pengawas',
            1
        );

        $this->assertSame(2, $count);

        $r1 = $this->sampelRepo->find($sId1);
        $r2 = $this->sampelRepo->find($sId2);

        $this->assertSame('DITERIMA', $r1['dok_pemutakhiran_status']);
        $this->assertSame('DITERIMA', $r1['peta_status']);
        $this->assertSame(100, (int) $r1['hasil_kk']);

        $this->assertSame('DITERIMA', $r2['dok_pemutakhiran_status']);
        $this->assertSame('DITERIMA', $r2['peta_status']);
        $this->assertSame(85, (int) $r2['hasil_kk']);
    }

    public function testPeminjamanDanPengembalianDokumen(): void
    {
        $pId = $this->createPeriodeAktif();
        $sId = $this->sampelRepo->add($pId, 1, 10, 100);
        $this->sampelRepo->assign($sId, 10, 20, 30);

        // Dokumen diterima di kantor
        $this->svc->terimaSatuan(
            $sId,
            ['terima_pemutakhiran' => 1, 'terima_peta' => 1],
            1
        );

        // Pinjam dokumen pemutakhiran oleh PML untuk klarifikasi
        $pinjamId = $this->svc->pinjamDokumen(
            $sId,
            'PEMUTAKHIRAN',
            20,
            'PML',
            '2026-03-13 09:00:00',
            'Cek anomali nomor urut KK 15',
            1
        );

        $this->assertGreaterThan(0, $pinjamId);

        $row = $this->sampelRepo->find($sId);
        $this->assertSame('DIPINJAM', $row['dok_pemutakhiran_status']);
        $this->assertSame('DITERIMA', $row['peta_status']);
        // Legacy flag tetap 1 karena sudah tercatat masuk
        $this->assertSame(1, (int) $row['dokumen_vsen']);

        // Kembalikan berkas ke kantor
        $this->svc->kembalikanDokumen(
            $pinjamId,
            '2026-03-13 16:30:00',
            'Sudah diklarifikasi dan diparaf',
            1
        );

        $rowAfter = $this->sampelRepo->find($sId);
        $this->assertSame('DITERIMA', $rowAfter['dok_pemutakhiran_status']);

        $pinjamRecord = $this->dokumenRepo->findPinjam($pinjamId);
        $this->assertSame('DIKEMBALIKAN', $pinjamRecord['status']);
        $this->assertSame('2026-03-13 16:30:00', $pinjamRecord['waktu_kembali']);
    }

    public function testTolakAksiJikaPeriodeBukanAktif(): void
    {
        $pRepo = new PeriodeRepository($this->pdo);
        $pId = $pRepo->create([
            'tahun' => 2026,
            'jenis' => 'SUSENAS_S1',
            'label' => 'Susenas DRAFT Test',
            'status' => 'DRAFT',
            'tgl_mulai' => '2026-09-01',
            'tgl_selesai' => '2026-09-30',
            'catatan' => 'DRAFT',
        ]);
        $sId = $this->sampelRepo->add($pId, 1, 10, 100);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('hanya dapat dilakukan pada periode AKTIF');

        $this->svc->terimaSatuan($sId, ['terima_pemutakhiran' => 1], 1);
    }
}
