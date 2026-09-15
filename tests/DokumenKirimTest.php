<?php

declare(strict_types=1);

namespace Tests;

use App\Repositories\AuditRepository;
use App\Repositories\SampelRepository;
use App\Repositories\SampelRutaRepository;
use App\Services\DokumenKirimService;
use PDO;
use PHPUnit\Framework\TestCase;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class DokumenKirimTest extends TestCase
{
    private PDO $pdo;
    private SampelRepository $sampelRepo;
    private SampelRutaRepository $rutaRepo;
    private DokumenKirimService $svc;
    private string $templatePath;

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
        foreach (['peminjaman_dokumen', 'penugasan', 'sampel_ruta', 'sampel', 'periode', 'sls', 'desa', 'kecamatan', 'audit_logs', 'users', 'orang_alias', 'orang', 'roles'] as $t) {
            $this->pdo->exec("TRUNCATE TABLE $t");
        }
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=1');

        $this->pdo->exec("INSERT INTO roles (id,code,label) VALUES (1,'ADMIN','Admin'),(2,'OPERATOR','Operator')");
        $this->pdo->exec("INSERT INTO users (id,email,password_hash,nama,role_id) VALUES (1,'op@bps.go.id','hash','Operator Test',2)");
        $this->pdo->exec("INSERT INTO kecamatan (kode,nama) VALUES ('170','SUMBERBARU')");
        $this->pdo->exec("INSERT INTO desa (id,kecamatan_kode,kode,nama) VALUES (1,'170','001','YOSORATI')");
        $this->pdo->exec("INSERT INTO sls (id,desa_id,kec,desa,sls,sub,kode_full,nks,nama_sls) VALUES (1,1,'170','001','0001','00','3509170001000100','56001','RT 01 RW 01')");

        $this->sampelRepo = new SampelRepository($this->pdo);
        $this->rutaRepo = new SampelRutaRepository($this->pdo);
        $this->svc = new DokumenKirimService($this->pdo, $this->rutaRepo, new AuditRepository($this->pdo));
        $this->templatePath = dirname(__DIR__) . '/data/template_dokkirimkab _15092026.xls';
    }

    private function createPeriodeAktif(): int
    {
        $pRepo = new \App\Repositories\PeriodeRepository($this->pdo);
        $id = $pRepo->create([
            'tahun' => 2026,
            'jenis' => 'SUSENAS_S2',
            'label' => 'Susenas September 2026 Test',
            'status' => 'DRAFT',
            'tgl_mulai' => '2026-09-01',
            'tgl_selesai' => '2026-09-30',
            'catatan' => 'Test',
        ]);
        $pRepo->setStatus($id, 'AKTIF');
        return $id;
    }

    /** Buat periode aktif + 1 sampel (NKS 56001). @return array{periode:int,sampel:int} */
    private function buatPeriodeSatuSampel(): array
    {
        $pId = $this->createPeriodeAktif();
        $sId = $this->sampelRepo->add($pId, 1, 10, 100);
        return ['periode' => $pId, 'sampel' => $sId];
    }

    public function testInisialisasiSepuluhRutaPerNks(): void
    {
        $ids = $this->buatPeriodeSatuSampel();
        $this->svc->initRutaForSampel($ids['sampel']);
        $rows = $this->rutaRepo->getBySampelId($ids['sampel']);
        $this->assertCount(10, $rows);
        $this->assertSame(1, (int) $rows[0]['no_urut_ruta']);
        $this->assertSame(10, (int) $rows[9]['no_urut_ruta']);
        $this->assertSame('BELUM', $rows[0]['status_selesai']);
        // idempoten: init ulang tidak menambah baris
        $this->svc->initRutaForSampel($ids['sampel']);
        $this->assertCount(10, $this->rutaRepo->getBySampelId($ids['sampel']));
    }

    /**
     * Baca daftar NKS unik dari sheet data template (baris 4+ kolom C).
     * @return array<int,string>
     */
    private function nksDariTemplate(): array
    {
        $ss = IOFactory::load($this->templatePath);
        try {
            $sheet = $ss->getSheetByName('data') ?? $ss->getSheet(0);
            $nks = [];
            for ($r = 4; $r <= $sheet->getHighestRow(); $r++) {
                $v = trim((string) $sheet->getCell('C' . $r)->getValue());
                if ($v !== '') {
                    $nks[str_pad($v, 5, '0', STR_PAD_LEFT)] = true;
                }
            }
            return array_keys($nks);
        } finally {
            $ss->disconnectWorksheets();
        }
    }

    /**
     * Buat periode aktif dengan sampel sesuai seluruh NKS pada template.
     * @return array{periode:int}
     */
    private function buatPeriodeSampelTemplate(): array
    {
        $pId = $this->createPeriodeAktif();
        $nksList = $this->nksDariTemplate();
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        $idSls = 2;
        foreach ($nksList as $nks) {
            $nksPad = (string) $nks;
            $kodeFull = '350917' . '001' . str_pad((string) $idSls, 3, '0', STR_PAD_LEFT) . str_pad((string) $idSls, 4, '0', STR_PAD_LEFT) . '00';
            $kodeFull = substr($kodeFull, 0, 16);
            $stmt = $this->pdo->prepare(
                "INSERT INTO sls (id,desa_id,kec,desa,sls,sub,kode_full,nks,nama_sls)
                 VALUES (:id,1,'170','001',:sls,'00',:kf,:nks,:nm)"
            );
            $stmt->execute([':id' => $idSls, ':sls' => str_pad((string) $idSls, 4, '0', STR_PAD_LEFT), ':kf' => $kodeFull, ':nks' => $nksPad, ':nm' => 'RT ' . $idSls]);
            $this->sampelRepo->add($pId, $idSls, 10, 100);
            $idSls++;
        }
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        return ['periode' => $pId];
    }

    public function testImportTemplateRiil(): void
    {
        $ids = $this->buatPeriodeSampelTemplate();
        $rekap = $this->svc->importExcel($ids['periode'], $this->templatePath, 1, '127.0.0.1', 'phpunit');

        $this->assertSame(280, $rekap['total']);
        $this->assertSame(280, $rekap['terupdate']);
        $this->assertSame(0, $rekap['dilewati']);
        $this->assertSame([], $rekap['errors']);

        // 24 ruta SUDAH, sisanya BELUM; total baris = jumlah NKS x 10
        $nNks = count($this->nksDariTemplate());
        $total = $this->pdo->query('SELECT COUNT(*) FROM sampel_ruta')->fetchColumn();
        $this->assertSame($nNks * 10, (int) $total);
        $sudah = (int) $this->pdo->query("SELECT COUNT(*) FROM sampel_ruta WHERE status_selesai='SUDAH'")->fetchColumn();
        $this->assertSame(24, $sudah);
        $belum = (int) $this->pdo->query("SELECT COUNT(*) FROM sampel_ruta WHERE status_selesai='BELUM'")->fetchColumn();
        $this->assertSame($nNks * 10 - 24, $belum);

        // Tanggal terkirim yang terisi harus 2026-09-14
        $tgl = $this->pdo->query("SELECT DISTINCT tgl_pengiriman FROM sampel_ruta WHERE tgl_pengiriman IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
        $this->assertSame(['2026-09-14'], $tgl);
    }

    public function testExportSesuaiStrukturTemplate(): void
    {
        $ids = $this->buatPeriodeSampelTemplate();
        $this->svc->importExcel($ids['periode'], $this->templatePath, 1, '127.0.0.1', 'phpunit');

        $path = $this->svc->exportExcel($ids['periode']);
        $this->assertFileExists($path);

        $ss = IOFactory::load($path);
        try {
            $data = $ss->getSheetByName('data');
            $contoh = $ss->getSheetByName('contoh');
            $this->assertNotNull($data);
            $this->assertNotNull($contoh);
            $this->assertSame('Data Progress Pengiriman Kuesioner Susenas ke Kabupaten', (string) $data->getCell('A1')->getValue());
            $this->assertSame('TT-BB-TTTT', (string) $data->getCell('H3')->getValue());
            $this->assertSame('kode prop [2 digit]', (string) $data->getCell('A2')->getValue());
            // 280 baris data mulai baris 4
            $this->assertSame(283, $data->getHighestRow());
            $this->assertSame('contoh', $contoh->getTitle());
            $this->assertSame('sudah', (string) $contoh->getCell('E4')->getValue());
            // Baris data pertama punya NKS 5 digit (dipadukan leading zero)
            $c4 = (string) $data->getCell('C4')->getValue();
            $this->assertSame(5, strlen($c4));
        } finally {
            $ss->disconnectWorksheets();
            @unlink($path);
        }
    }

    public function testUpdateSatuanDanSelesaiSemua(): void
    {
        $ids = $this->buatPeriodeSatuSampel();
        $sId = $ids['sampel'];
        $this->svc->initRutaForSampel($sId);

        // Quick update 1 baris: ruta 3 selesai + modul + KP
        $this->svc->updateRutaSatuan($sId, [
            'no_urut_ruta' => 3,
            'status_selesai' => 'SUDAH',
            'catatan_modul' => 1,
            'catatan_kp' => 1,
            'tgl_pengiriman' => '2026-09-14',
            'ttd_sos' => 'AB',
            'ttd_ipds' => 'CD',
        ], 1, '127.0.0.1', 'phpunit');

        $rows = $this->rutaRepo->getBySampelId($sId);
        $this->assertSame('SUDAH', $rows[2]['status_selesai']);
        $this->assertSame(1, (int) $rows[2]['catatan_modul']);
        $this->assertSame(1, (int) $rows[2]['catatan_kp']);
        $this->assertSame('2026-09-14', (string) $rows[2]['tgl_pengiriman']);
        $this->assertSame('AB', $rows[2]['ttd_sos']);
        $this->assertSame('CD', $rows[2]['ttd_ipds']);
        $this->assertSame('BELUM', $rows[0]['status_selesai']);

        // Summary periode
        $sum = $this->rutaRepo->getSummaryByPeriodeId($ids['periode']);
        $this->assertSame(1, $sum[$sId]['selesai']);
        $this->assertSame(1, $sum[$sId]['modul']);
        $this->assertSame(1, $sum[$sId]['kp']);

        // Batch selesai semua
        $this->svc->setSemuaRutaSelesai($sId, '2026-09-15', 'ZZ', 1, '127.0.0.1', 'phpunit');
        $rows2 = $this->rutaRepo->getBySampelId($sId);
        foreach ($rows2 as $r) {
            $this->assertSame('SUDAH', $r['status_selesai']);
            $this->assertSame('2026-09-15', (string) $r['tgl_pengiriman']);
            $this->assertSame('ZZ', $r['ttd_sos']);
        }
        $sum2 = $this->rutaRepo->getSummaryByPeriodeId($ids['periode']);
        $this->assertSame(10, $sum2[$sId]['selesai']);
    }
}
