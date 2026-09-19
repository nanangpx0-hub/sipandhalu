<?php

declare(strict_types=1);

namespace Tests;

use App\Repositories\AuditRepository;
use App\Repositories\SampelRepository;
use App\Repositories\SampelRutaRepository;
use App\Services\PengolahanService;
use PDO;
use PHPUnit\Framework\TestCase;

final class PengolahanTest extends TestCase
{
    private PDO $pdo;
    private SampelRutaRepository $rutaRepo;
    private PengolahanService $svc;

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
        foreach (['jadwal_pengawas_pengolahan', 'peminjaman_dokumen', 'penugasan', 'sampel_ruta', 'sampel', 'periode', 'sls', 'desa', 'kecamatan', 'audit_logs', 'users', 'orang_alias', 'orang', 'roles'] as $t) {
            $this->pdo->exec("TRUNCATE TABLE `$t`");
        }
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=1');

        $this->pdo->exec("INSERT INTO roles (id,code,label) VALUES
            (1,'ADMIN','Admin'),
            (2,'OPERATOR','Operator'),
            (3,'PML','Pengawas Lapangan'),
            (4,'PCL','Pencacah Lapangan'),
            (5,'PENGOLAH','Pengolah Data'),
            (19,'PENGAWAS_OLAH','Pengawas Pengolahan')");

        $this->pdo->exec("INSERT INTO orang (id, nama, role_id) VALUES
            (1, 'Pengolah Satu', 5),
            (2, 'PCL Satu', 4),
            (3, 'PML Satu', 3),
            (11, 'Arumita Hertriesa', 19),
            (12, 'Wahyu Wijayanti', 19)");

        $this->pdo->exec("INSERT INTO users (id, email, password_hash, nama, role_id, orang_id) VALUES 
            (1, 'admin@bps.go.id', 'hash', 'Admin', 1, NULL),
            (2, 'pengolah1@bps.go.id', 'hash', 'Pengolah Satu', 5, 1)");

        $this->pdo->exec("INSERT INTO kecamatan (kode, nama) VALUES ('020','GUMUKMAS')");
        $this->pdo->exec("INSERT INTO desa (id, kecamatan_kode, kode, nama) VALUES (1,'020','003','MENAMPU')");
        $this->pdo->exec("INSERT INTO sls (id, desa_id, kec, desa, sls, sub, kode_full, nks, nama_sls) VALUES 
            (1, 1, '020', '003', '0001', '00', '3509020003000100', '50536', 'RT 01 RW 01')");

        $this->pdo->exec("INSERT INTO periode (id, tahun, jenis, label, status) VALUES 
            (1, 2026, 'SUSENAS_S2', 'Susenas Sept 2026', 'AKTIF')");

        $this->pdo->exec("INSERT INTO sampel (id, periode_id, sls_id, target_sampel) VALUES (1, 1, 1, 10)");
        $this->pdo->exec("INSERT INTO penugasan (sampel_id, pcl_id, pml_id, pengolah_id) VALUES (1, 2, 3, 1)");

        // Jadwal pengawas S2 2026 (subset 3 baris untuk uji cepat)
        $this->pdo->exec("INSERT INTO jadwal_pengawas_pengolahan (periode_id, tanggal, orang_id, nama_pengawas, hari, status, keterangan) VALUES
            (1, '2026-09-15', 11, 'Arumita Hertriesa', 'Selasa', 'TUGAS', 'Piket pengolahan Susenas S2 2026'),
            (1, '2026-09-16', 12, 'Wahyu Wijayanti', 'Rabu', 'TUGAS', 'Piket pengolahan Susenas S2 2026'),
            (1, '2026-09-19', NULL, '-', 'Sabtu', 'LIBUR', 'Libur akhir pekan')");

        $this->rutaRepo = new SampelRutaRepository($this->pdo);
        $this->rutaRepo->ensureDefaults(1);

        $this->svc = new PengolahanService($this->pdo, $this->rutaRepo, new AuditRepository($this->pdo));
    }

    public function testGetPengolahanRutaDanSummary(): void
    {
        $all = $this->svc->getDaftarRuta(1, [], ['role' => 'ADMIN']);
        $this->assertCount(10, $all);
        $this->assertSame('50536', $all[0]['nks']);
        $this->assertSame('Pengolah Satu', $all[0]['nama_pengolah']);

        $summary = $this->svc->getSummary(1);
        $this->assertSame(10, (int) $summary['total_ruta']);
        $this->assertSame(0, (int) $summary['dok_ada']);
        $this->assertSame(10, (int) $summary['dok_belum']);
    }

    public function testUpdateRutaStatusDanCatatan(): void
    {
        $admin = ['id' => 1, 'role' => 'ADMIN', 'orang_id' => null];
        $first = $this->rutaRepo->getBySampelId(1)[0];
        $rutaId = (int) $first['id'];

        $updateData = [
            'status_dokumen' => 'ADA',
            'status_transfer_k' => 1,
            'status_transfer_kp' => 1,
            'ket_kp_pengolah' => 'Error R603 berkode 1 tapi rincian kosong',
            'ket_kp_lapangan' => 'Sudah dikonfirmasi ke KRT, isian benar berkode 2',
            'ket_kp_sosial' => 'Disetujui ganti kode 2',
            'uji_petik_pengawas' => 'Validasi sampling sesuai'
        ];

        $res = $this->svc->updateRuta($rutaId, $updateData, $admin);
        $this->assertSame('ADA', $res['status_dokumen']);
        $this->assertSame(1, (int) $res['status_transfer_k']);
        $this->assertSame(1, (int) $res['status_transfer_kp']);
        $this->assertSame(1, (int) $res['catatan_kp']); // otomatis flag terisi
        $this->assertSame('Error R603 berkode 1 tapi rincian kosong', $res['ket_kp_pengolah']);
        $this->assertSame('Sudah dikonfirmasi ke KRT, isian benar berkode 2', $res['ket_kp_lapangan']);
        $this->assertSame('Disetujui ganti kode 2', $res['ket_kp_sosial']);
        $this->assertSame('Validasi sampling sesuai', $res['uji_petik_pengawas']);

        // Verifikasi audit log
        $audit = $this->pdo->query("SELECT * FROM audit_logs WHERE tabel_target='sampel_ruta' AND id_target='{$rutaId}'")->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($audit);
        $this->assertSame('UPDATE', $audit['aksi']);
    }

    public function testExportLkExcelMultiSheet(): void
    {
        $spreadsheet = $this->svc->exportLkExcel(1);
        $sheetNames = $spreadsheet->getSheetNames();

        $this->assertContains('Alokasi', $sheetNames);
        $this->assertContains('Rekap', $sheetNames);
        $this->assertContains('Pengolah Satu', $sheetNames);

        $shRekap = $spreadsheet->getSheetByName('Rekap');
        $this->assertSame('No.', $shRekap->getCell('A1')->getValue());
        $this->assertSame('50536', (string) $shRekap->getCell('B2')->getValue());
        $this->assertSame(1, (int) $shRekap->getCell('E2')->getValue());
    }

    public function testPetugasPengolahanBisaTransferKDanKp(): void
    {
        $pengolahUser = ['id' => 2, 'role' => 'PENGOLAH', 'orang_id' => 1];
        $first = $this->rutaRepo->getBySampelId(1)[0];
        $rutaId = (int) $first['id'];

        // Petugas pengolahan melakukan Transfer K dan Transfer KP
        $res = $this->svc->updateRuta($rutaId, [
            'status_transfer_k' => 1,
            'status_transfer_kp' => 1,
            'status_dokumen' => 'ADA'
        ], $pengolahUser);

        $this->assertSame(1, (int) $res['status_transfer_k']);
        $this->assertSame(1, (int) $res['status_transfer_kp']);
        $this->assertSame('ADA', $res['status_dokumen']);
    }

    public function testBatchTransferOlehPetugasPengolahan(): void
    {
        $pengolahUser = ['id' => 2, 'role' => 'PENGOLAH', 'orang_id' => 1];

        // Batch transfer K untuk seluruh ruta di NKS 50536
        $count = $this->svc->batchTransfer([
            'periode_id' => 1,
            'nks' => '50536',
            'field' => 'status_transfer_k',
            'value' => 1,
        ], $pengolahUser);

        $this->assertSame(10, $count);

        // Batch transfer KP untuk seluruh ruta di NKS 50536
        $countKp = $this->svc->batchTransfer([
            'periode_id' => 1,
            'nks' => '50536',
            'field' => 'status_transfer_kp',
            'value' => 1,
        ], $pengolahUser);

        $this->assertSame(10, $countKp);

        // Verifikasi semua ruta di NKS 50536 sudah berstatus transfer
        $rutas = $this->rutaRepo->getBySampelId(1);
        foreach ($rutas as $r) {
            $this->assertSame(1, (int) $r['status_transfer_k']);
            $this->assertSame(1, (int) $r['status_transfer_kp']);
        }
    }

    public function testNonPengolahDitolakTransferK(): void
    {
        $pclUser = ['id' => 10, 'role' => 'PCL', 'orang_id' => 2];
        $first = $this->rutaRepo->getBySampelId(1)[0];
        $rutaId = (int) $first['id'];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transfer K (Kor), Transfer KP, dan status dokumen hanya dapat dilakukan oleh petugas pengolahan.');

        $this->svc->updateRuta($rutaId, [
            'status_transfer_k' => 1
        ], $pclUser);
    }

    public function testAutoFillMetadataPenerimaanDokumenFisik(): void
    {
        $pengolahUser = ['id' => 2, 'role' => 'PENGOLAH', 'orang_id' => 1, 'nama' => 'Pengolah Satu'];
        $first = $this->rutaRepo->getBySampelId(1)[0];
        $rutaId = (int) $first['id'];

        // 1. Ubah status dokumen menjadi ADA tanpa mengisi tanggal & ttd
        $res = $this->svc->updateRuta($rutaId, [
            'status_dokumen' => 'ADA'
        ], $pengolahUser);

        $this->assertSame('ADA', $res['status_dokumen']);
        $this->assertSame(date('Y-m-d'), $res['tgl_pengiriman']);
        $this->assertSame('PML Satu (Tim Sosial)', $res['ttd_sos']);
        $this->assertSame('Pengolah Satu (Tim IPDS)', $res['ttd_ipds']);

        // 2. Ubah kembali status dokumen menjadi BELUM, metadata harus direset
        $resBelum = $this->svc->updateRuta($rutaId, [
            'status_dokumen' => 'BELUM'
        ], $pengolahUser);

        $this->assertSame('BELUM', $resBelum['status_dokumen']);
        $this->assertNull($resBelum['tgl_pengiriman']);
        $this->assertNull($resBelum['ttd_sos']);
        $this->assertNull($resBelum['ttd_ipds']);
    }

    public function testBatchTerimaDokumenOlehPetugasPengolahan(): void
    {
        $pengolahUser = ['id' => 2, 'role' => 'PENGOLAH', 'orang_id' => 1, 'nama' => 'Pengolah Satu'];

        $count = $this->svc->batchTerimaDokumen([
            'periode_id' => 1,
            'nks' => '50536',
            'tgl_pengiriman' => '2026-09-14',
            'ttd_sos' => 'PML Sukses (Tim Sosial)',
            'ttd_ipds' => 'Pengolah Satu (Tim IPDS)',
        ], $pengolahUser);

        $this->assertSame(10, $count);

        $rutas = $this->rutaRepo->getBySampelId(1);
        foreach ($rutas as $r) {
            $this->assertSame('ADA', $r['status_dokumen']);
            $this->assertSame('2026-09-14', $r['tgl_pengiriman']);
            $this->assertSame('PML Sukses (Tim Sosial)', $r['ttd_sos']);
            $this->assertSame('Pengolah Satu (Tim IPDS)', $r['ttd_ipds']);
        }
    }

    public function testNonPengolahDitolakBatchTerimaDokumen(): void
    {
        $pclUser = ['id' => 10, 'role' => 'PCL', 'orang_id' => 2];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Penerimaan dokumen fisik hanya dapat dilakukan oleh petugas pengolahan.');

        $this->svc->batchTerimaDokumen([
            'periode_id' => 1,
            'nks' => '50536',
        ], $pclUser);
    }

    public function testExportLkExcelTermasukKolomPenerimaan(): void
    {
        // Set 1 ruta menjadi ADA
        $pengolahUser = ['id' => 2, 'role' => 'PENGOLAH', 'orang_id' => 1, 'nama' => 'Pengolah Satu'];
        $first = $this->rutaRepo->getBySampelId(1)[0];
        $this->svc->updateRuta((int) $first['id'], ['status_dokumen' => 'ADA'], $pengolahUser);

        $spreadsheet = $this->svc->exportLkExcel(1);
        $shRekap = $spreadsheet->getSheetByName('Rekap');

        $this->assertSame('Tanggal Terima', (string) $shRekap->getCell('G1')->getValue());
        $this->assertSame('Diserahkan Oleh (Tim Sosial)', (string) $shRekap->getCell('H1')->getValue());
        $this->assertSame('Diterima Oleh (Tim IPDS)', (string) $shRekap->getCell('I1')->getValue());
        $this->assertSame('STATUS TRANSFER K', (string) $shRekap->getCell('M1')->getValue());

        $this->assertSame('Ada', (string) $shRekap->getCell('F2')->getValue());
        $this->assertSame(date('Y-m-d'), (string) $shRekap->getCell('G2')->getValue());
        $this->assertSame('PML Satu (Tim Sosial)', (string) $shRekap->getCell('H2')->getValue());
        $this->assertSame('Pengolah Satu (Tim IPDS)', (string) $shRekap->getCell('I2')->getValue());
    }

    public function testJadwalPengawasPerPeriode(): void
    {
        $jadwal = $this->svc->getJadwalPengawas(1);
        $this->assertCount(3, $jadwal);
        $this->assertSame('2026-09-15', $jadwal[0]['tanggal']);
        $this->assertSame('Selasa', $jadwal[0]['hari']);
        $this->assertSame('Arumita Hertriesa', $jadwal[0]['nama_pengawas']);
        $this->assertSame('TUGAS', $jadwal[0]['status']);
        $this->assertSame(11, (int) $jadwal[0]['orang_id']);

        // Urut tanggal menaik
        $this->assertSame('2026-09-16', $jadwal[1]['tanggal']);
        $this->assertSame('2026-09-19', $jadwal[2]['tanggal']);
        $this->assertSame('LIBUR', $jadwal[2]['status']);
    }

    public function testPengawasHariIniAktifDanLibur(): void
    {
        $aktif = $this->svc->getPengawasHariIni(1, '2026-09-15');
        $this->assertNotNull($aktif);
        $this->assertSame('Arumita Hertriesa', $aktif['nama_pengawas']);
        $this->assertSame('TUGAS', $aktif['status']);

        $aktif2 = $this->svc->getPengawasHariIni(1, '2026-09-16');
        $this->assertNotNull($aktif2);
        $this->assertSame('Wahyu Wijayanti', $aktif2['nama_pengawas']);

        // Hari libur → NULL
        $this->assertNull($this->svc->getPengawasHariIni(1, '2026-09-19'));
        // Di luar rentang → NULL
        $this->assertNull($this->svc->getPengawasHariIni(1, '2026-10-10'));
        // Format salah → NULL
        $this->assertNull($this->svc->getPengawasHariIni(1, '15-09-2026'));
    }

    public function testExportLkExcelMemuatSheetJadwal(): void
    {
        $spreadsheet = $this->svc->exportLkExcel(1);
        $sheetNames = $spreadsheet->getSheetNames();
        $this->assertContains('Jadwal Pengawas Pengolahan', $sheetNames);

        $sh = $spreadsheet->getSheetByName('Jadwal Pengawas Pengolahan');
        $this->assertSame('No', (string) $sh->getCell('A1')->getValue());
        $this->assertSame('Hari', (string) $sh->getCell('B1')->getValue());
        $this->assertSame('Tanggal', (string) $sh->getCell('C1')->getValue());
        $this->assertSame('Bulan', (string) $sh->getCell('D1')->getValue());
        $this->assertSame('Tahun', (string) $sh->getCell('E1')->getValue());
        $this->assertSame('Nama Pengawas Pengolahan', (string) $sh->getCell('F1')->getValue());
        $this->assertSame('Status', (string) $sh->getCell('G1')->getValue());

        // 3 baris data uji
        $this->assertSame(1, (int) $sh->getCell('A2')->getValue());
        $this->assertSame('Selasa', (string) $sh->getCell('B2')->getValue());
        $this->assertSame('Arumita Hertriesa', (string) $sh->getCell('F2')->getValue());
        $this->assertSame('TUGAS', (string) $sh->getCell('G2')->getValue());
        $this->assertSame('LIBUR', (string) $sh->getCell('G4')->getValue());
        $this->assertGreaterThanOrEqual(4, $sh->getHighestRow());
    }
}

