<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Database;
use App\Repositories\AuditRepository;
use App\Repositories\PeriodeRepository;
use App\Repositories\SampelRutaRepository;
use App\Services\PengolahanService;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class SerutiPengolahanTest extends TestCase
{
    private PDO $pdo;
    private SampelRutaRepository $rutaRepo;
    private PeriodeRepository $periodeRepo;
    private PengolahanService $service;

    protected function setUp(): void
    {
        $this->pdo = Database::connection();
        $this->rutaRepo = new SampelRutaRepository($this->pdo);
        $this->periodeRepo = new PeriodeRepository($this->pdo);
        $this->service = new PengolahanService(
            $this->pdo,
            $this->rutaRepo,
            new AuditRepository($this->pdo)
        );
    }

    /**
     * Uji 1: Verifikasi 28 NKS terdaftar lengkap di 4 periode Seruti (Q1 s/d Q4) tahun 2026.
     */
    public function testDaftarSampelSerutiEmpatTriwulan2026(): void
    {
        $userNks = [
            '00327', '00407', '00445', '01356', '01692', '02185', '03125', '03376',
            '04098', '04167', '04592', '50536', '51611', '51797', '52097', '52471',
            '53400', '54904', '55394', '56326', '56361', '56901', '57672', '58988',
            '59905', '60488', '60552', '61110'
        ];

        $quarters = ['SERUTI_Q1', 'SERUTI_Q2', 'SERUTI_Q3', 'SERUTI_Q4'];

        foreach ($quarters as $qJenis) {
            $stmt = $this->pdo->prepare("SELECT id FROM periode WHERE tahun = 2026 AND jenis = :jenis LIMIT 1");
            $stmt->execute([':jenis' => $qJenis]);
            $pid = (int) $stmt->fetchColumn();

            $this->assertGreaterThan(0, $pid, "Periode {$qJenis} harus terdaftar di database.");

            // Cek jumlah sampel SLS
            $stmtSls = $this->pdo->prepare("
                SELECT sl.nks 
                FROM sampel sp 
                JOIN sls sl ON sl.id = sp.sls_id 
                WHERE sp.periode_id = :pid
                ORDER BY sl.nks ASC
            ");
            $stmtSls->execute([':pid' => $pid]);
            $nksList = $stmtSls->fetchAll(PDO::FETCH_COLUMN);

            $this->assertCount(28, $nksList, "Periode {$qJenis} harus memiliki tepat 28 NKS sampel.");
            $this->assertEquals($userNks, $nksList, "NKS di {$qJenis} harus cocok 100% dengan 28 NKS yang ditentukan.");

            // Cek jumlah ruta (28 x 10 = 280)
            $stmtRuta = $this->pdo->prepare("
                SELECT COUNT(sr.id) 
                FROM sampel_ruta sr
                JOIN sampel sp ON sp.id = sr.sampel_id
                WHERE sp.periode_id = :pid
            ");
            $stmtRuta->execute([':pid' => $pid]);
            $totalRuta = (int) $stmtRuta->fetchColumn();

            $this->assertSame(280, $totalRuta, "Periode {$qJenis} harus memiliki total 280 ruta.");
        }
    }

    /**
     * Uji 2: Hak akses transfer dapat dilakukan oleh Pengolah, Pengawas Pengolahan, Operator, dan Admin.
     * Namun khusus transfer Seruti HANYA boleh dilakukan oleh Operator, Pengawas Pengolahan, dan Admin.
     */
    public function testHakAksesTransferPengolahan(): void
    {
        $admin = ['role_code' => 'ADMIN'];
        $operator = ['role_code' => 'OPERATOR'];
        $smPls = ['role_code' => 'SM_PLS'];
        $pengolah = ['role_code' => 'PENGOLAH', 'orang_id' => 2];
        $pengawas = ['role_code' => 'PENGAWAS_OLAH'];
        $pcl = ['role_code' => 'PCL', 'orang_id' => 9];
        $viewer = ['role_code' => 'VIEWER'];

        // canTransfer (Umum: K dan KP)
        $this->assertTrue($this->service->canTransfer($admin));
        $this->assertTrue($this->service->canTransfer($operator));
        $this->assertTrue($this->service->canTransfer($smPls));
        $this->assertTrue($this->service->canTransfer($pengolah));
        $this->assertTrue($this->service->canTransfer($pengawas));

        $this->assertFalse($this->service->canTransfer($pcl));
        $this->assertFalse($this->service->canTransfer($viewer));

        // canTransferSeruti (Khusus Seruti: Operator, Pengawas Pengolahan, Admin)
        $this->assertTrue($this->service->canTransferSeruti($admin));
        $this->assertTrue($this->service->canTransferSeruti($operator));
        $this->assertTrue($this->service->canTransferSeruti($smPls));
        $this->assertTrue($this->service->canTransferSeruti($pengawas));

        // Pengolah TIDAK BISA melakukan transfer Seruti
        $this->assertFalse($this->service->canTransferSeruti($pengolah));
        $this->assertFalse($this->service->canTransferSeruti($pcl));
        $this->assertFalse($this->service->canTransferSeruti($viewer));
    }

    /**
     * Uji 2B: Pengolah ditolak (403) saat mencoba melakukan mutasi transfer Seruti.
     */
    public function testPengolahDitolakMutasiTransferSeruti(): void
    {
        // Ambil ruta Susenas milik Anung (pengolah_id = 2)
        $stmt = $this->pdo->query("
            SELECT sr.id FROM sampel_ruta sr
            JOIN sampel sp ON sp.id = sr.sampel_id
            JOIN penugasan pg ON pg.sampel_id = sp.id
            WHERE sp.periode_id = 1 AND pg.pengolah_id = 2
            LIMIT 1
        ");
        $rutaId = (int) $stmt->fetchColumn();
        $this->assertGreaterThan(0, $rutaId);

        $pengolah = ['id' => 2, 'role_code' => 'PENGOLAH', 'orang_id' => 2, 'nama' => 'Anung'];

        $beforeRuta = $this->rutaRepo->findRutaById($rutaId);
        $targetVal = ((int) ($beforeRuta['status_transfer_seruti'] ?? 0) === 1) ? 0 : 1;

        // 1. Uji via updateRuta
        $caughtUpdate = false;
        try {
            $this->service->updateRuta($rutaId, ['status_transfer_seruti' => $targetVal], $pengolah);
        } catch (RuntimeException $e) {
            $caughtUpdate = true;
            $this->assertSame(403, $e->getCode());
            $this->assertStringContainsString('Hanya Operator, Pengawas Pengolahan, dan Admin', $e->getMessage());
        }
        $this->assertTrue($caughtUpdate, 'Pengolah seharusnya DITOLAK saat mencoba update status_transfer_seruti.');

        // 2. Uji via batchTransfer
        $caughtBatch = false;
        try {
            $this->service->batchTransfer([
                'periode_id' => 1,
                'ruta_ids' => [$rutaId],
                'field' => 'status_transfer_seruti',
                'value' => $targetVal,
            ], $pengolah);
        } catch (RuntimeException $e) {
            $caughtBatch = true;
            $this->assertSame(403, $e->getCode());
            $this->assertStringContainsString('Hanya Operator, Pengawas Pengolahan, dan Admin', $e->getMessage());
        }
        $this->assertTrue($caughtBatch, 'Pengolah seharusnya DITOLAK saat mencoba batchTransfer status_transfer_seruti.');
    }

    /**
     * Uji 3: Kuesioner Susenas yang belum selesai diolah ditolak saat ditransfer ke Seruti.
     */
    public function testSusenasBelumSelesaiDitolakTransferSeruti(): void
    {
        // Cari ruta Susenas S2 (periode_id = 1) yang belum selesai
        $stmt = $this->pdo->query("
            SELECT sr.id FROM sampel_ruta sr
            JOIN sampel sp ON sp.id = sr.sampel_id
            WHERE sp.periode_id = 1 AND (sr.status_dokumen = 'BELUM' OR sr.status_transfer_k = 0 OR sr.status_transfer_kp = 0)
            LIMIT 1
        ");
        $rutaId = (int) $stmt->fetchColumn();
        $this->assertGreaterThan(0, $rutaId);

        $admin = ['id' => 1, 'role_code' => 'ADMIN', 'nama' => 'Admin Test'];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Kuesioner Susenas di NKS ini belum selesai diolah');

        // Mencoba transfer seruti
        $this->service->updateRuta($rutaId, ['status_transfer_seruti' => 1], $admin);
    }

    /**
     * Uji 4: Kuesioner Susenas yang selesai diolah berhasil ditransfer ke Seruti.
     * Dan mengaktifkan sampel Seruti yang berpasangan.
     */
    public function testSusenasSelesaiBerhasilTransferSerutiDanMengaktifkanSeruti(): void
    {
        // Ambil ruta Susenas S2 untuk NKS 52097 ruta 10
        $stmt = $this->pdo->query("
            SELECT sr.id, sp.sls_id, sr.no_urut_ruta
            FROM sampel_ruta sr
            JOIN sampel sp ON sp.id = sr.sampel_id
            JOIN sls sl ON sl.id = sp.sls_id
            WHERE sp.periode_id = 1 AND sl.nks = '52097' AND sr.no_urut_ruta = 10
            LIMIT 1
        ");
        $susRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($susRow);
        $susRutaId = (int) $susRow['id'];

        // Cari ruta Seruti Q3 (periode_id = 6) yang berpasangan
        $stmtSeruti = $this->pdo->prepare("
            SELECT sr.id
            FROM sampel_ruta sr
            JOIN sampel sp ON sp.id = sr.sampel_id
            JOIN sls sl ON sl.id = sp.sls_id
            WHERE sp.periode_id = 6 AND sl.nks = '52097' AND sr.no_urut_ruta = 10
            LIMIT 1
        ");
        $stmtSeruti->execute();
        $serutiRutaId = (int) $stmtSeruti->fetchColumn();
        $this->assertGreaterThan(0, $serutiRutaId);

        $admin = ['id' => 1, 'role_code' => 'ADMIN', 'nama' => 'Admin Test'];
        $pengawas = ['id' => 4, 'role_code' => 'PENGAWAS_OLAH', 'nama' => 'Pengawas Test'];

        try {
            // 1. Sebelum transfer Susenas: cek ruta Seruti Q3
            $serutiBefore = $this->rutaRepo->findRutaById($serutiRutaId);
            $this->assertSame(0, (int) $serutiBefore['is_seruti_aktif'], "Sampel Seruti harus belum aktif sebelum transfer Susenas.");

            // Percobaan pengawas atau pengolah mengubah ruta Seruti yang belum aktif harus ditolak 403
            $failedToBlock = false;
            try {
                $this->service->updateRuta($serutiRutaId, ['status_transfer_k' => 1], $pengawas);
                $failedToBlock = true;
            } catch (RuntimeException $e) {
                $this->assertSame(403, $e->getCode());
                $this->assertStringContainsString('Sampel Seruti untuk NKS ini belum aktif', $e->getMessage());
            }
            $this->assertFalse($failedToBlock, "Ruta Seruti belum aktif harus memblokir mutasi.");

            // 2. Set Susenas selesai diolah (ADA, K=1, KP=1) dan lakukan transfer Seruti
            $this->pdo->prepare("
                UPDATE sampel_ruta
                SET status_dokumen = 'ADA', status_transfer_k = 1, status_transfer_kp = 1
                WHERE id = :id
            ")->execute([':id' => $susRutaId]);

            // Pengawas pengolahan melakukan transfer Seruti pada dokumen Susenas
            $updatedSus = $this->service->updateRuta($susRutaId, ['status_transfer_seruti' => 1], $pengawas);
            $this->assertSame(1, (int) $updatedSus['status_transfer_seruti']);

            // 3. Setelah transfer Susenas: cek kembali ruta Seruti Q3
            $serutiAfter = $this->rutaRepo->findRutaById($serutiRutaId);
            $this->assertSame(1, (int) $serutiAfter['is_seruti_aktif'], "Sampel Seruti sekarang harus AKTIF (is_seruti_aktif = 1) setelah transfer Susenas.");

            // Pengawas pengolahan sekarang BISA mengolah kuesioner Seruti
            $updatedSeruti = $this->service->updateRuta($serutiRutaId, ['status_transfer_k' => 1], $pengawas);
            $this->assertSame(1, (int) $updatedSeruti['status_transfer_k']);

        } finally {
            // Bersihkan / revert state
            $this->pdo->prepare("UPDATE sampel_ruta SET status_transfer_seruti = 0 WHERE id = :id")->execute([':id' => $susRutaId]);
            $this->pdo->prepare("UPDATE sampel_ruta SET status_transfer_k = 0 WHERE id = :id")->execute([':id' => $serutiRutaId]);
        }
    }
}
