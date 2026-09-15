<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditRepository;
use App\Repositories\SampelRutaRepository;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use InvalidArgumentException;
use PDO;
use RuntimeException;

/**
 * Pemantauan pengiriman kuesioner tingkat rumah tangga (Dokumen Kirim Kab).
 * Layout Excel mengikuti template `template_dokkirimkab`:
 * sheet `data` (judul baris 1, header baris 2, format tanggal baris 3, data mulai baris 4)
 * dan sheet `contoh` (satu baris contoh).
 */
final class DokumenKirimService
{
    private const HEADER_ROW = 2;
    private const DATA_START_ROW = 4;
    private const HEADERS = [
        'kode prop [2 digit]',
        'kode kab [2 digit]',
        'kode NKS  [5 digit]',
        'No Urut Ruta  [max: 2 digit]',
        "Sudah\nSelesai? [sudah/belum]",
        "Blok \nCatatan \n(MODUL) \nTerisi  \nYa = 1 \nTidak = \n0 \n",
        "Blok \nCatatan \n(KP) \nTerisi  \nYa\n= 1 \nTidak = \n0 \n",
        'TGL_PENGIRIMAN',
        'TTD Tim Sos',
        'TTD Tim IPDS',
    ];

    public function __construct(
        private PDO $pdo,
        private SampelRutaRepository $rutaRepo,
        private AuditRepository $auditRepo
    ) {
    }

    /** Pastikan 10 baris ruta default tersedia untuk satu sampel. */
    public function initRutaForSampel(int $sampelId): void
    {
        $this->rutaRepo->ensureDefaults($sampelId);
    }

    /** Peta NKS => sampel_id untuk satu periode (validasi impor). @return array<string,int> */
    private function nksMap(int $periodeId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT sp.id AS sampel_id, s.nks
             FROM sampel sp JOIN sls s ON s.id = sp.sls_id
             WHERE sp.periode_id = :p'
        );
        $stmt->execute([':p' => $periodeId]);
        $map = [];
        foreach ($stmt->fetchAll() as $r) {
            $map[str_pad(trim((string) $r['nks']), 5, '0', STR_PAD_LEFT)] = (int) $r['sampel_id'];
        }
        return $map;
    }

    /** Ubah tanggal dari template (TT-BB-TTTT / numeric / DateTime) menjadi Y-m-d atau ''. */
    private function parseTanggal(mixed $v): string
    {
        if ($v === null || $v === '') {
            return '';
        }
        if ($v instanceof \DateTimeInterface) {
            return $v->format('Y-m-d');
        }
        if (is_float($v) || is_int($v)) {
            return ExcelDate::excelToDateTimeObject((float) $v)->format('Y-m-d');
        }
        $s = trim((string) $v);
        if (preg_match('/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{4})$/', $s, $m) === 1) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }
        if (preg_match('/^(\d{4})[-\/.](\d{1,2})[-\/.](\d{1,2})$/', $s, $m) === 1) {
            return sprintf('%04d-%02d-%02d', (int) $m[1], (int) $m[2], (int) $m[3]);
        }
        $t = strtotime($s);
        return $t === false ? '' : date('Y-m-d', $t);
    }

    /**
     * Impor file template Dok Kirim Kab.
     * @return array{total:int,terupdate:int,dilewati:int,errors:array<int,string>}
     */
    public function importExcel(int $periodeId, string $filePath, ?int $actorId, ?string $ip, ?string $ua): array
    {
        $map = $this->nksMap($periodeId);
        if ($map === []) {
            throw new RuntimeException('Tidak ada sampel pada periode ini. Tambahkan sampel terlebih dulu.');
        }

        $ss = IOFactory::load($filePath);
        try {
            $sheet = $ss->getSheetByName('data') ?? $ss->getSheet(0);
            $highestRow = $sheet->getHighestRow();

            $total = 0;
            $dilewati = 0;
            $errors = [];
            $rows = [];

            for ($r = self::DATA_START_ROW; $r <= $highestRow; $r++) {
                $nks = trim((string) $sheet->getCell('C' . $r)->getValue());
                $noUrut = trim((string) $sheet->getCell('D' . $r)->getValue());
                if ($nks === '' && $noUrut === '') {
                    continue; // baris kosong
                }
                $total++;
                $nks = str_pad($nks, 5, '0', STR_PAD_LEFT);
                $noUrut = (int) $noUrut;

                if (!isset($map[$nks])) {
                    $errors[] = sprintf('Baris %d: NKS %s tidak ditemukan pada periode ini.', $r, $nks);
                    $dilewati++;
                    continue;
                }
                if ($noUrut < 1 || $noUrut > 10) {
                    $errors[] = sprintf('Baris %d: No Urut Ruta tidak valid (1-10).', $r);
                    $dilewati++;
                    continue;
                }

                $e = mb_strtolower(trim((string) $sheet->getCell('E' . $r)->getValue()));
                $f = (string) $sheet->getCell('F' . $r)->getValue();
                $g = (string) $sheet->getCell('G' . $r)->getValue();
                $tgl = $this->parseTanggal($sheet->getCell('H' . $r)->getValue());
                $ttdSos = trim((string) $sheet->getCell('I' . $r)->getValue());
                $ttdIpds = trim((string) $sheet->getCell('J' . $r)->getValue());

                $rows[] = [
                    'sampel_id' => $map[$nks],
                    'no_urut_ruta' => $noUrut,
                    'data' => [
                        'status_selesai' => ($e === 'sudah' || $e === '1') ? 'SUDAH' : 'BELUM',
                        'catatan_modul' => (int) ($f === '1'),
                        'catatan_kp' => (int) ($g === '1'),
                        'tgl_pengiriman' => $tgl,
                        'ttd_sos' => $ttdSos,
                        'ttd_ipds' => $ttdIpds,
                    ],
                ];
            }
        } finally {
            $ss->disconnectWorksheets();
        }

        $terupdate = 0;
        if ($rows !== []) {
            $this->rutaRepo->batchUpsert($rows);
            $terupdate = count($rows);
        }

        $rekap = ['total' => $total, 'terupdate' => $terupdate, 'dilewati' => $dilewati, 'errors' => $errors];
        $this->auditRepo->log(
            $actorId,
            'IMPORT',
            'sampel_ruta',
            (string) $periodeId,
            null,
            ['rekap' => $rekap, 'periode_id' => $periodeId],
            $ip ?? '',
            $ua ?? ''
        );

        return $rekap;
    }

    /**
     * Ekspor rekap dok kirim kab satu periode sebagai file Excel yang presisi
     * mengikuti layout template. @return string path sementara file xlsx.
     */
    public function exportExcel(int $periodeId): string
    {
        $stmt = $this->pdo->prepare(
            'SELECT s.nks, sr.no_urut_ruta, sr.status_selesai, sr.catatan_modul, sr.catatan_kp,
                    sr.tgl_pengiriman, sr.ttd_sos, sr.ttd_ipds
             FROM sampel_ruta sr
             JOIN sampel sp ON sp.id = sr.sampel_id
             JOIN sls s ON s.id = sp.sls_id
             WHERE sp.periode_id = :p
             ORDER BY s.nks ASC, sr.no_urut_ruta ASC'
        );
        $stmt->execute([':p' => $periodeId]);
        $data = $stmt->fetchAll();

        $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle('data');
        $this->writeLayoutHeader($sheet);
        $r = self::DATA_START_ROW;
        foreach ($data as $d) {
            $this->writeDataRow($sheet, $r, $d);
            $r++;
        }
        $this->styleDataBlock($sheet, max($r - 1, self::DATA_START_ROW));

        $contoh = $ss->createSheet();
        $contoh->setTitle('contoh');
        $this->writeLayoutHeader($contoh);
        $contoh->setCellValue('A4', '11');
        $contoh->setCellValue('B4', '12');
        $contoh->setCellValueExplicit('C4', '02345', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $contoh->setCellValue('D4', 1);
        $contoh->setCellValue('E4', 'sudah');
        $contoh->setCellValue('F4', 1);
        $contoh->setCellValue('G4', 1);
        $this->writeTanggalCell($contoh, 4, '2023-03-13');
        $this->styleDataBlock($contoh, self::DATA_START_ROW);

        $path = (string) tempnam(sys_get_temp_dir(), 'dokkirim_') . '.xlsx';
        (new Xlsx($ss))->save($path);
        $ss->disconnectWorksheets();
        return $path;
    }

    /** Tulis baris 1 (judul), baris 2 (header 10 kolom), baris 3 (format tanggal TT-BB-TTTT). */
    private function writeLayoutHeader(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        $sheet->setCellValue('A1', 'Data Progress Pengiriman Kuesioner Susenas ke Kabupaten');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheet->fromArray(self::HEADERS, null, 'A' . self::HEADER_ROW);
        $sheet->getStyle('A' . self::HEADER_ROW . ':J' . self::HEADER_ROW)->getFont()->setBold(true);
        $sheet->getStyle('A' . self::HEADER_ROW . ':J' . self::HEADER_ROW)->getAlignment()
            ->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . self::HEADER_ROW . ':J' . self::HEADER_ROW)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCE6F1');
        $sheet->setCellValue('H3', 'TT-BB-TTTT');
        $sheet->getStyle('H3')->getFont()->setItalic(true);
        $widths = [8, 8, 10, 8, 10, 9, 9, 14, 14, 14];
        foreach ($widths as $i => $w) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i + 1))->setWidth($w);
        }
    }

    /** Tulis 1 baris data ruta. */
    private function writeDataRow(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $r, array $d): void
    {
        $sheet->setCellValue('A' . $r, '35');
        $sheet->setCellValue('B' . $r, '09');
        $sheet->setCellValueExplicit('C' . $r, str_pad(trim((string) $d['nks']), 5, '0', STR_PAD_LEFT), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('D' . $r, (int) $d['no_urut_ruta']);
        if ($d['status_selesai'] === 'SUDAH') {
            $sheet->setCellValue('E' . $r, 'sudah');
        }
        if ((int) $d['catatan_modul'] === 1) {
            $sheet->setCellValue('F' . $r, 1);
        }
        if ((int) $d['catatan_kp'] === 1) {
            $sheet->setCellValue('G' . $r, 1);
        }
        if ($d['tgl_pengiriman'] !== null && $d['tgl_pengiriman'] !== '') {
            $this->writeTanggalCell($sheet, $r, (string) $d['tgl_pengiriman']);
        }
        $sheet->setCellValue('I' . $r, (string) ($d['ttd_sos'] ?? ''));
        $sheet->setCellValue('J' . $r, (string) ($d['ttd_ipds'] ?? ''));
    }

    /** Tulis sel tanggal dengan format Excel DD-MM-YYYY (TT-BB-TTTT). */
    private function writeTanggalCell(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $r, string $tgl): void
    {
        $cell = $sheet->getCell('H' . $r);
        $cell->setValue(ExcelDate::PHPToExcel(new \DateTimeImmutable($tgl)));
        $cell->getStyle()->getNumberFormat()->setFormatCode('DD-MM-YYYY');
    }

    /** Border tipis seluruh blok data. */
    private function styleDataBlock(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $lastRow): void
    {
        $sheet->getStyle('A' . self::DATA_START_ROW . ':J' . $lastRow)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    /** Update satu baris ruta (dari UI rincian). */
    public function updateRutaSatuan(int $sampelId, array $dataRuta, ?int $actorId, ?string $ip, ?string $ua): void
    {
        $noUrut = (int) ($dataRuta['no_urut_ruta'] ?? 0);
        if ($noUrut < 1 || $noUrut > 10) {
            throw new InvalidArgumentException('No Urut Ruta harus 1-10.');
        }
        $this->initRutaForSampel($sampelId);
        $before = $this->rutaRepo->getBySampelId($sampelId);
        $this->rutaRepo->upsertRuta($sampelId, $noUrut, $dataRuta);
        $after = $this->rutaRepo->getBySampelId($sampelId);
        $this->auditRepo->log(
            $actorId,
            'UPDATE',
            'sampel_ruta',
            (string) $sampelId,
            ['no_urut_ruta' => $noUrut, 'before' => $before[$noUrut - 1] ?? null],
            ['no_urut_ruta' => $noUrut, 'after' => $after[$noUrut - 1] ?? null],
            $ip ?? '',
            $ua ?? ''
        );
    }

    /** Tandai seluruh 10 ruta satu sampel selesai sekaligus. */
    public function setSemuaRutaSelesai(int $sampelId, string $tglPengiriman, ?string $ttd, ?int $actorId, ?string $ip, ?string $ua): void
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tglPengiriman) !== 1) {
            throw new InvalidArgumentException('Tanggal pengiriman harus format YYYY-MM-DD.');
        }
        $this->initRutaForSampel($sampelId);
        $affected = $this->rutaRepo->markAllDone($sampelId, $tglPengiriman, $ttd);
        $this->auditRepo->log(
            $actorId,
            'UPDATE',
            'sampel_ruta',
            (string) $sampelId,
            null,
            ['aksi' => 'selesai_semua', 'tgl_pengiriman' => $tglPengiriman, 'ttd' => $ttd, 'baris' => $affected],
            $ip ?? '',
            $ua ?? ''
        );
    }
}
