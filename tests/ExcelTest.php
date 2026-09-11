<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Excel;
use PHPUnit\Framework\TestCase;

final class ExcelTest extends TestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        $this->tmp = sys_get_temp_dir() . '/sipandhalu_excel_test_' . getmypid() . '.xlsx';
    }

    protected function tearDown(): void
    {
        if (is_file($this->tmp)) {
            unlink($this->tmp);
        }
    }

    public function testRoundTripSaveDanRead(): void
    {
        Excel::save($this->tmp, ['Nama', 'No HP', 'Status'], [
            ['Junaidi Ari Siswanto', '081234567890', 'Aktif'],
            ['Aminatus Sholeha', '081298765432', 'Nonaktif'],
        ], 'Petugas', [['PETUNJUK'], ['1. Jangan ubah header.']]);

        $this->assertFileExists($this->tmp);
        $data = Excel::readFirstSheet($this->tmp);
        $this->assertSame(['Nama', 'No HP', 'Status'], $data['headers']);
        $this->assertCount(2, $data['rows']);
        $this->assertSame('Junaidi Ari Siswanto', Excel::pick($data['rows'][0], ['Nama']));
        $this->assertSame('081234567890', Excel::pick($data['rows'][0], ['No HP', 'HP']));
        $this->assertSame('081298765432', Excel::pick($data['rows'][1], ['No HP']));
        // baris tanpa isi apa pun dibuang
        $this->assertSame('', Excel::pick($data['rows'][0] ?? [], ['KolomTidakAda']));
    }

    public function testPickFleksibelDanStatus(): void
    {
        $row = ['No. HP' => '08123', '  Nama Lengkap ' => 'Budi', 'STATUS' => 'Nonaktif'];
        $this->assertSame('08123', Excel::pick($row, ['no hp', 'nohp']));
        $this->assertSame('Budi', Excel::pick($row, ['Nama', 'Nama Lengkap']));
        $this->assertSame('default', Excel::pick($row, ['Tidak Ada'], 'default'));
        $this->assertSame(0, Excel::statusToAktif('Nonaktif'));
        $this->assertSame(0, Excel::statusToAktif('non aktif'));
        $this->assertSame(1, Excel::statusToAktif('Aktif'));
        $this->assertSame(1, Excel::statusToAktif(''));
        $this->assertSame(1, Excel::statusToAktif('apapun'));
    }

    public function testParseDate(): void
    {
        $serial = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(new \DateTimeImmutable('2026-02-17'));
        $this->assertSame('2026-02-17', Excel::parseDate((string) $serial));
        $this->assertSame('2026-02-17', Excel::parseDate('2026-2-17'));
        $this->assertSame('2026-02-17', Excel::parseDate('17/2/2026'));   // d/m/y (konvensi ID)
        $this->assertSame('2026-02-17', Excel::parseDate('20260217'));
        $this->assertSame('', Excel::parseDate(''));
    }

    public function testImportPetugasEndToEnd(): void
    {
        // simulasi alur import: file Excel -> baris -> validasi serperti controller
        $cfg = require dirname(__DIR__) . '/config/database.php';
        $cfg['name'] = 'sipandhalu_test';
        $pdo = new \PDO(
            sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']),
            $cfg['user'], $cfg['pass'], $cfg['options']
        );
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        foreach (['audit_logs', 'orang_alias', 'orang'] as $t) {
            $pdo->exec("TRUNCATE TABLE $t");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

        Excel::save($this->tmp, ['Nama', 'No HP', 'Email', 'Alamat', 'Status'], [
            ['junaidi ari SISWANTO', '081234567890', 'junaidi@mail.id', 'Jl. Bhayangkara 12', 'Aktif'],
            ['junaidi ari siswanto', '', '', '', ''],        // duplikat -> gagal
            ['A', '', '', '', ''],                            // nama terlalu pendek -> gagal
            ['Aminatus Sholeha', '081298765432', '', '', 'Nonaktif'],
            ['', '0812', '', '', ''],                         // nama kosong -> dilewati
        ]);
        $data = Excel::readFirstSheet($this->tmp);
        $this->assertCount(5, $data['rows']);

        $svc = new \App\Services\OrangService($pdo, new \App\Repositories\OrangRepository($pdo), new \App\Repositories\AuditRepository($pdo));
        $ok = 0;
        $errors = [];
        foreach ($data['rows'] as $i => $row) {
            $in = [
                'nama' => Excel::pick($row, ['Nama']),
                'no_hp' => Excel::pick($row, ['No HP']),
                'email' => Excel::pick($row, ['Email']),
                'alamat' => Excel::pick($row, ['Alamat']),
                'is_aktif' => Excel::statusToAktif(Excel::pick($row, ['Status'])),
            ];
            if ($in['nama'] === '') {
                $errors[] = "Baris " . ($i + 2);
                continue;
            }
            $res = $svc->create($in, null, '127.0.0.1', 'phpunit');
            if ($res['ok']) {
                $ok++;
            } else {
                $errors[] = "Baris " . ($i + 2) . ': ' . implode(' ', $res['errors']);
            }
        }
        $this->assertSame(2, $ok);
        $this->assertCount(3, $errors);
        $repo = new \App\Repositories\OrangRepository($pdo);
        $amin = $repo->findByNormalized('aminatus sholeha');
        $this->assertNotNull($amin);
        $this->assertSame(0, (int) $amin['is_aktif']); // status Nonaktif terbaca
        $jun = $repo->findByNormalized('junaidi ari siswanto');
        $this->assertSame('081234567890', $jun['no_hp']);
    }
}
