<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Database;
use App\Repositories\AuditRepository;
use App\Repositories\PeriodeRepository;
use App\Repositories\SampelRutaRepository;
use App\Services\SerutiService;
use PDO;
use PHPUnit\Framework\TestCase;

final class SerutiModulTest extends TestCase
{
    private PDO $pdo;
    private SerutiService $svc;

    protected function setUp(): void
    {
        $cfg = require dirname(__DIR__) . '/config/database.php';
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']
        );
        $this->pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], $cfg['options']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->svc = new SerutiService(
            $this->pdo,
            new SampelRutaRepository($this->pdo),
            new PeriodeRepository($this->pdo),
            new AuditRepository($this->pdo)
        );
    }

    // ════════════════════════════════════════════════════════════════
    // GROUP 1: ROUTE TESTS
    // ════════════════════════════════════════════════════════════════

    public function testRouteSerutiIndexExists(): void
    {
        $routes = file_get_contents(dirname(__DIR__) . '/config/routes.php');
        $this->assertStringContainsString("'/seruti'", $routes);
        $this->assertStringContainsString("SerutiController::class, 'index'", $routes);
    }

    public function testRouteSerutiUpdateRutaExists(): void
    {
        $routes = file_get_contents(dirname(__DIR__) . '/config/routes.php');
        $this->assertStringContainsString("'/seruti/update-ruta'", $routes);
        $this->assertStringContainsString("SerutiController::class, 'updateRuta'", $routes);
    }

    public function testRouteSerutiBatchTransferExists(): void
    {
        $routes = file_get_contents(dirname(__DIR__) . '/config/routes.php');
        $this->assertStringContainsString("'/seruti/batch-transfer'", $routes);
        $this->assertStringContainsString("SerutiController::class, 'batchTransfer'", $routes);
    }

    public function testRouteSerutiExportExists(): void
    {
        $routes = file_get_contents(dirname(__DIR__) . '/config/routes.php');
        $this->assertStringContainsString("'/seruti/export'", $routes);
        $this->assertStringContainsString("SerutiController::class, 'export'", $routes);
    }

    public function testSerutiControllerHasIndexMethod(): void
    {
        $ctrl = new \App\Controllers\SerutiController();
        $ref = new \ReflectionClass($ctrl);
        $this->assertTrue($ref->hasMethod('index'));
        $this->assertTrue($ref->getMethod('index')->isPublic());
    }

    public function testSerutiControllerHasUpdateRutaMethod(): void
    {
        $ctrl = new \App\Controllers\SerutiController();
        $ref = new \ReflectionClass($ctrl);
        $this->assertTrue($ref->hasMethod('updateRuta'));
        $this->assertTrue($ref->getMethod('updateRuta')->isPublic());
    }

    public function testSerutiControllerHasBatchTransferMethod(): void
    {
        $ctrl = new \App\Controllers\SerutiController();
        $ref = new \ReflectionClass($ctrl);
        $this->assertTrue($ref->hasMethod('batchTransfer'));
        $this->assertTrue($ref->getMethod('batchTransfer')->isPublic());
    }

    public function testSerutiControllerHasExportMethod(): void
    {
        $ctrl = new \App\Controllers\SerutiController();
        $ref = new \ReflectionClass($ctrl);
        $this->assertTrue($ref->hasMethod('export'));
        $this->assertTrue($ref->getMethod('export')->isPublic());
    }

    // ════════════════════════════════════════════════════════════════
    // GROUP 2: RBAC TESTS
    // ════════════════════════════════════════════════════════════════

    public function testDatabaseConnection()
    {
        $stmt = $this->pdo->query("SELECT DATABASE() AS db_name");
        $db = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($db['db_name']);
    }

    public function testCanTransferSerutiTrueForAdmin(): void
    {
        $this->assertTrue($this->svc->canTransferSeruti(['role_code' => 'ADMIN']));
    }

    public function testCanTransferSerutiTrueForOperator(): void
    {
        $this->assertTrue($this->svc->canTransferSeruti(['role_code' => 'OPERATOR']));
    }

    public function testCanTransferSerutiTrueForSmPls(): void
    {
        $this->assertTrue($this->svc->canTransferSeruti(['role_code' => 'SM_PLS']));
    }

    public function testCanTransferSerutiTrueForPengawasOlah(): void
    {
        $this->assertTrue($this->svc->canTransferSeruti(['role_code' => 'PENGAWAS_OLAH']));
    }

    public function testCanTransferSerutiFalseForPengolah(): void
    {
        $this->assertFalse($this->svc->canTransferSeruti(['role_code' => 'PENGOLAH']));
    }

    public function testCanTransferSerutiFalseForViewer(): void
    {
        $this->assertFalse($this->svc->canTransferSeruti(['role_code' => 'VIEWER']));
    }

    public function testCanTransferSerutiFalseForPml(): void
    {
        $this->assertFalse($this->svc->canTransferSeruti(['role_code' => 'PML']));
    }

    public function testCanTransferSerutiFalseForPcl(): void
    {
        $this->assertFalse($this->svc->canTransferSeruti(['role_code' => 'PCL']));
    }

    public function testCanEditTrueForAdmin(): void
    {
        $this->assertTrue($this->svc->canEdit(['role_code' => 'ADMIN']));
    }

    public function testCanEditTrueForOperator(): void
    {
        $this->assertTrue($this->svc->canEdit(['role_code' => 'OPERATOR']));
    }

    public function testCanEditTrueForPengolah(): void
    {
        $this->assertTrue($this->svc->canEdit(['role_code' => 'PENGOLAH']));
    }

    public function testCanEditTrueForPengawasOlah(): void
    {
        $this->assertTrue($this->svc->canEdit(['role_code' => 'PENGAWAS_OLAH']));
    }

    public function testCanEditTrueForSmPls(): void
    {
        $this->assertTrue($this->svc->canEdit(['role_code' => 'SM_PLS']));
    }

    public function testCanEditFalseForViewer(): void
    {
        $this->assertFalse($this->svc->canEdit(['role_code' => 'VIEWER']));
    }

    public function testCanEditFalseForPml(): void
    {
        $this->assertFalse($this->svc->canEdit(['role_code' => 'PML']));
    }

    // ════════════════════════════════════════════════════════════════
    // GROUP 3: SERVICE METHOD TESTS
    // ════════════════════════════════════════════════════════════════

    public function testGetSerutiPeriodesReturnsFour(): void
    {
        $periodes = $this->svc->getSerutiPeriodes();
        $this->assertCount(4, $periodes);
        $jenisList = array_column($periodes, 'jenis');
        $this->assertContains('SERUTI_Q1', $jenisList);
        $this->assertContains('SERUTI_Q2', $jenisList);
        $this->assertContains('SERUTI_Q3', $jenisList);
        $this->assertContains('SERUTI_Q4', $jenisList);
    }

    public function testGetDefaultSerutiPeriodeIdReturnsValid(): void
    {
        $id = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $id);
        $stmt = $this->pdo->prepare("SELECT * FROM periode WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        $this->assertNotFalse($row);
        $this->assertStringStartsWith('SERUTI_', (string) ($row['jenis'] ?? ''));
    }

    public function testGetDefaultSerutiPeriodeIdOverride(): void
    {
        $periodes = $this->svc->getSerutiPeriodes();
        if ($periodes === []) {
            $this->markTestSkipped('No Seruti periods available');
        }
        $firstId = (int) $periodes[0]['id'];
        $id = $this->svc->getDefaultSerutiPeriodeId($firstId);
        $this->assertSame($firstId, $id);
    }

    public function testGetDaftarRutaReturnsData(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $user = ['role_code' => 'ADMIN', 'orang_id' => 0];
        $rutas = $this->svc->getDaftarRuta($periodeId, [], $user);
        $this->assertNotEmpty($rutas);
        $this->assertGreaterThanOrEqual(1, count($rutas));
    }

    public function testGetDaftarRutaPENGOLAHScope(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);

        $user = ['role_code' => 'PENGOLAH', 'orang_id' => 1];
        $rutas = $this->svc->getDaftarRuta($periodeId, [], $user);
        foreach ($rutas as $r) {
            $this->assertSame(1, (int) ($r['pengolah_id'] ?? 0));
        }
    }

    public function testGetDaftarRutaPENGOLAHScopeRestricted(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);

        $userAll = ['role_code' => 'ADMIN', 'orang_id' => 0];
        $userPengolah = ['role_code' => 'PENGOLAH', 'orang_id' => 1];

        $adminRutas = $this->svc->getDaftarRuta($periodeId, [], $userAll);
        $pengolahRutas = $this->svc->getDaftarRuta($periodeId, [], $userPengolah);

        $this->assertGreaterThanOrEqual(0, count($pengolahRutas));
        $this->assertLessThanOrEqual(count($adminRutas), count($pengolahRutas));
        foreach ($pengolahRutas as $r) {
            $this->assertSame(1, (int) ($r['pengolah_id'] ?? 0));
        }
    }

    public function testGetKpiReturnsRequiredKeys(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $kpi = $this->svc->getKpi($periodeId);
        $this->assertArrayHasKey('total_sampel', $kpi);
        $this->assertArrayHasKey('susenas_selesai', $kpi);
        $this->assertArrayHasKey('susenas_selesai_pct', $kpi);
        $this->assertArrayHasKey('siap_olah', $kpi);
        $this->assertArrayHasKey('terkunci', $kpi);
        $this->assertArrayHasKey('selesai_transfer', $kpi);
    }

    public function testKpiValuesNonNegative(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $kpi = $this->svc->getKpi($periodeId);
        foreach (['total_sampel', 'susenas_selesai', 'siap_olah', 'terkunci', 'selesai_transfer'] as $key) {
            $this->assertGreaterThanOrEqual(0, (int) ($kpi[$key] ?? -1), "$key harus >= 0");
        }
        $this->assertGreaterThanOrEqual(0, (float) ($kpi['susenas_selesai_pct'] ?? -1));
    }

    public function testKpiTotalEqualsSiapOlahPlusTerkunci(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $kpi = $this->svc->getKpi($periodeId);
        $this->assertSame(
            (int) ($kpi['total_sampel'] ?? 0),
            (int) ($kpi['siap_olah'] ?? 0) + (int) ($kpi['terkunci'] ?? 0)
        );
    }

    public function testGetNKSListReturnsArray(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $nksList = $this->svc->getNKSList($periodeId);
        $this->assertIsArray($nksList);
        $this->assertNotEmpty($nksList);
    }

    public function testGetNKSListForPENGOLAHScope(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $nksAdmin = $this->svc->getNKSList($periodeId, -1);
        $nksPengolah = $this->svc->getNKSList($periodeId, 1);
        $this->assertNotEmpty($nksAdmin);
        $this->assertNotEmpty($nksPengolah);
        foreach ($nksPengolah as $nks) {
            $this->assertContains($nks, $nksAdmin);
        }
    }

    public function testCountNKSReturnsInteger(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $count = $this->svc->countNKS($periodeId);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testGetPengolahListReturnsArray(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $list = $this->svc->getPengolahList($periodeId);
        $this->assertIsArray($list);
    }

    public function testCountRutaByPengolahReturnsInt(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $count = $this->svc->countRutaByPengolah($periodeId, 1);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testExportDataReturnsStructure(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $user = ['role_code' => 'ADMIN', 'orang_id' => 0];
        $data = $this->svc->exportData($periodeId, [], $user);
        $this->assertArrayHasKey('headers', $data);
        $this->assertArrayHasKey('rows', $data);
        $this->assertIsArray($data['headers']);
        $this->assertIsArray($data['rows']);
        $this->assertNotEmpty($data['headers']);
    }

    // ════════════════════════════════════════════════════════════════
    // GROUP 4: RBAC ENFORCEMENT TESTS
    // ════════════════════════════════════════════════════════════════

    public function testAssertCanTransferSerutiThrowsForPENGOLAH(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(403);
        $user = ['role_code' => 'PENGOLAH', 'orang_id' => 1];
        $this->svc->assertCanTransferSeruti(1, $user);
    }

    public function testAssertCanTransferSerutiThrowsForViewer(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(403);
        $user = ['role_code' => 'VIEWER'];
        $this->svc->assertCanTransferSeruti(1, $user);
    }

    public function testAssertCanEditRutaThrowsForUnauthorized(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(403);
        $user = ['role_code' => 'VIEWER'];
        $this->svc->assertCanEditRuta(1, $user);
    }

    public function testBatchTransferRejectsPENGOLAH(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(403);
        $user = ['role_code' => 'PENGOLAH', 'orang_id' => 1];
        $this->svc->batchTransfer([
            'periode_id' => $this->svc->getDefaultSerutiPeriodeId(),
            'value' => 1,
        ], $user);
    }

    public function testUpdateRutaPENGOLAHCannotTransferSeruti(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(403);
        $user = ['role_code' => 'PENGOLAH', 'orang_id' => 1];
        $this->svc->updateRuta(1, ['status_transfer_seruti' => 1], $user);
    }

    public function testUpdateRutaForViewerThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(403);
        $user = ['role_code' => 'VIEWER'];
        $this->svc->updateRuta(1, ['catatan_seruti' => 'test'], $user);
    }

    // ════════════════════════════════════════════════════════════════
    // GROUP 5: INTEGRATION & EDGE TESTS
    // ════════════════════════════════════════════════════════════════

    public function testRutaDataHasRequiredColumns(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $user = ['role_code' => 'ADMIN', 'orang_id' => 0];
        $rutas = $this->svc->getDaftarRuta($periodeId, [], $user);
        if (empty($rutas)) {
            $this->markTestSkipped('No ruta data available');
        }
        $requiredCols = ['nks', 'id', 'no_urut_ruta', 'nama_pengolah', 'is_seruti_aktif', 'status_transfer_seruti'];
        $firstRow = $rutas[0];
        foreach ($requiredCols as $col) {
            $this->assertArrayHasKey($col, $firstRow, "Kolom $col harus ada");
        }
    }

    public function testExportDataHeadersMatchSpec(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $user = ['role_code' => 'ADMIN', 'orang_id' => 0];
        $data = $this->svc->exportData($periodeId, [], $user);
        $expectedHeaders = [
            'No.', 'NKS', 'Kecamatan', 'Desa', 'No. Urut Ruta',
            'Petugas Pengolah', 'Petugas Pengawas',
            'Prasyarat Susenas', 'Status Seruti', 'Transfer Seruti',
            'Catatan Kendali Mutu Seruti',
        ];
        $this->assertSame($expectedHeaders, $data['headers']);
    }

    public function testExportDataRowsHaveData(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $user = ['role_code' => 'ADMIN', 'orang_id' => 0];
        $data = $this->svc->exportData($periodeId, [], $user);
        $this->assertNotEmpty($data['rows'], 'Export harus memiliki data baris');
        $firstRow = $data['rows'][0];
        $this->assertArrayHasKey('nks', $firstRow);
        $this->assertArrayHasKey('no_urut_ruta', $firstRow);
    }

    public function testKpiSusenasPctRange(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $kpi = $this->svc->getKpi($periodeId);
        $pct = (float) ($kpi['susenas_selesai_pct'] ?? -1);
        $this->assertGreaterThanOrEqual(0.0, $pct);
        $this->assertLessThanOrEqual(100.0, $pct);
    }

    public function testPENGOLAHScopingReturnsConsistentData(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);

        $userAll = ['role_code' => 'ADMIN', 'orang_id' => 0];
        $userPengolah = ['role_code' => 'PENGOLAH', 'orang_id' => 1];

        $allNKS = $this->svc->getNKSList($periodeId, -1);
        $pengolahNKS = $this->svc->getNKSList($periodeId, 1);
        $allRutas = $this->svc->getDaftarRuta($periodeId, [], $userAll);
        $pengolahRutas = $this->svc->getDaftarRuta($periodeId, [], $userPengolah);

        $allNKSSet = array_flip($allNKS);
        foreach ($pengolahNKS as $nks) {
            $this->assertArrayHasKey($nks, $allNKSSet, "NKS $nks tidak ada di daftar admin");
        }
        $this->assertLessThanOrEqual(count($allRutas), count($pengolahRutas));
    }

    public function testKpiForPENGOLAHScope(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $kpiAdmin = $this->svc->getKpi($periodeId, -1);
        $kpiPengolah = $this->svc->getKpi($periodeId, 1);
        $this->assertGreaterThanOrEqual(0, $kpiPengolah['total_sampel']);
        $this->assertLessThanOrEqual($kpiAdmin['total_sampel'], $kpiPengolah['total_sampel']);
    }

    public function testRutaCountForPengolah(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $count = $this->svc->countRutaByPengolah($periodeId, 1);
        $pengolahRutas = $this->svc->getDaftarRuta($periodeId, [], ['role_code' => 'PENGOLAH', 'orang_id' => 1]);
        $this->assertCount($count, $pengolahRutas, "countRutaByPengolah harus cocok dengan getDaftarRuta");
    }

    public function testGetPengolahListFiltered(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $listAll = $this->svc->getPengolahList($periodeId, -1);
        $listFiltered = $this->svc->getPengolahList($periodeId, 1);
        $this->assertIsArray($listAll);
        $this->assertIsArray($listFiltered);
        $this->assertLessThanOrEqual(count($listAll), count($listFiltered));
    }

    public function testExportDataEmptyFilters(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $user = ['role_code' => 'ADMIN', 'orang_id' => 0];
        $data = $this->svc->exportData($periodeId, ['q' => ''], $user);
        $this->assertNotEmpty($data['rows']);
    }

    public function testExportDataFiltersByKesiapan(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $user = ['role_code' => 'ADMIN', 'orang_id' => 0];
        $dataSiap = $this->svc->exportData($periodeId, ['status_kesiapan' => 'siap'], $user);
        $dataMenunggu = $this->svc->exportData($periodeId, ['status_kesiapan' => 'menunggu'], $user);
        $this->assertIsArray($dataSiap['rows']);
        $this->assertIsArray($dataMenunggu['rows']);
    }

    public function testKpiSerutiActiveSum(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $kpi = $this->svc->getKpi($periodeId);
        $total = (int) ($kpi['total_sampel'] ?? 0);
        $siap = (int) ($kpi['siap_olah'] ?? 0);
        $terkunci = (int) ($kpi['terkunci'] ?? 0);
        $this->assertSame($total, $siap + $terkunci);
    }

    public function testKpiSelesaiTransferNotExceedsTotal(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $kpi = $this->svc->getKpi($periodeId);
        $this->assertLessThanOrEqual((int) ($kpi['total_sampel'] ?? 0), (int) ($kpi['selesai_transfer'] ?? 0));
    }

    public function testControllerInstantiation(): void
    {
        $ctrl = new \App\Controllers\SerutiController();
        $this->assertInstanceOf(\App\Controllers\SerutiController::class, $ctrl);
    }

    public function testControllerHasRequiredProperties(): void
    {
        $ctrl = new \App\Controllers\SerutiController();
        $ref = new \ReflectionClass($ctrl);
        $this->assertTrue($ref->hasProperty('periodeRepo'));
        $this->assertTrue($ref->hasProperty('serutiSvc'));
        $this->assertTrue($ref->hasProperty('pdo'));
    }

    public function testServiceHasRequiredMethods(): void
    {
        $ref = new \ReflectionClass(SerutiService::class);
        $required = [
            'canTransferSeruti', 'canEdit', 'getSerutiPeriodes',
            'getDefaultSerutiPeriodeId', 'getDaftarRuta', 'getKpi',
            'countNKS', 'getNKSList', 'getPengolahList', 'countRutaByPengolah',
            'exportData', 'assertCanTransferSeruti', 'assertCanEditRuta',
            'updateRuta', 'batchTransfer',
        ];
        foreach ($required as $method) {
            $this->assertTrue($ref->hasMethod($method), "Method $method harus ada");
        }
    }

    public function testPENGOLAHCannotTransferViaService(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(403);
        $user = ['role_code' => 'PENGOLAH', 'orang_id' => 1, 'id' => 1, 'ip' => '127.0.0.1', 'user_agent' => 'test'];
        $this->svc->batchTransfer(['periode_id' => 1, 'value' => 1], $user);
    }

    public function testAdminCanTransferViaService(): void
    {
        $periodeId = $this->svc->getDefaultSerutiPeriodeId();
        $this->assertGreaterThan(0, $periodeId);
        $user = ['role_code' => 'ADMIN', 'orang_id' => 0, 'id' => 1, 'ip' => '127.0.0.1', 'user_agent' => 'test'];
        $result = $this->svc->batchTransfer(['periode_id' => $periodeId, 'ruta_ids' => [], 'value' => 1], $user);
        $this->assertIsInt($result);
    }

    public function testServiceConstantHttpForbidden(): void
    {
        $this->assertSame(403, SerutiService::HTTP_FORBIDDEN);
    }

    public function testServiceConstantHttpBadRequest(): void
    {
        $this->assertSame(400, SerutiService::HTTP_BAD_REQUEST);
    }
}
