<?php

declare(strict_types=1);

namespace App\Core;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Helper Excel (PhpSpreadsheet): ekspor unduhan xlsx, template, dan baca file impor.
 * Baris = array asosiatif ber-key nama kolom pada baris header pertama.
 */
final class Excel
{
    private const MAX_IMPORT_ROWS = 2000;

    /** Tulis xlsx ke path absolut (dipakai test / penyimpanan arsip). */
    public static function save(string $absPath, array $headers, array $rows, string $sheetTitle = 'Data', array $petunjuk = []): void
    {
        $ss = self::build($headers, $rows, $sheetTitle, $petunjuk);
        (new Xlsx($ss))->save($absPath);
        $ss->disconnectWorksheets();
    }

    /** Kirim xlsx sebagai unduhan browser lalu exit. */
    public static function download(string $filename, array $headers, array $rows, string $sheetTitle = 'Data', array $petunjuk = []): void
    {
        if (ob_get_level() > 0) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $ss = self::build($headers, $rows, $sheetTitle, $petunjuk);
        (new Xlsx($ss))->save('php://output');
        $ss->disconnectWorksheets();
        exit;
    }

    /**
     * Baca sheet pertama file Excel: baris 1 = header, baris berikutnya = data.
     * @return array{headers:array<int,string>, rows:array<int,array<string,string>>}
     */
    public static function readFirstSheet(string $path): array
    {
        $ss = IOFactory::load($path);
        try {
            $sheet = $ss->getSheet(0);
            $matrix = $sheet->toArray(null, true, false, false);
        } finally {
            $ss->disconnectWorksheets();
        }
        if ($matrix === []) {
            return ['headers' => [], 'rows' => []];
        }
        $headers = [];
        foreach ($matrix[0] as $i => $h) {
            $headers[$i] = trim((string) $h);
        }
        $rows = [];
        foreach (array_slice($matrix, 1) as $line) {
            $assoc = [];
            $adaIsi = false;
            foreach ($headers as $i => $h) {
                if ($h === '') {
                    continue;
                }
                $val = self::cellToString($line[$i] ?? null);
                $assoc[$h] = $val;
                if ($val !== '') {
                    $adaIsi = true;
                }
            }
            if ($adaIsi) {
                $rows[] = $assoc;
            }
        }
        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * Ambil nilai kolom dengan pencocokan fleksibel (case/spasi/tanda baca diabaikan).
     * @param array<int,string> $keys urutan alternatif nama kolom
     */
    public static function pick(array $row, array $keys, string $default = ''): string
    {
        foreach ($keys as $k) {
            $nk = self::keyNorm($k);
            if ($nk === '') {
                continue;
            }
            foreach ($row as $hk => $v) {
                if (self::keyNorm((string) $hk) === $nk) {
                    return trim((string) $v);
                }
            }
        }
        return $default;
    }

    /** 'Aktif/1/on' -> 1 ; 'Nonaktif/0/off/non aktif/tidak' -> 0 ; kosong -> 1 (default). */
    public static function statusToAktif(string $s): int
    {
        $n = self::keyNorm($s);
        if ($n === '') {
            return 1;
        }
        return in_array($n, ['nonaktif', 'tidak', 'n', 'non', 'off', '0'], true) ? 0 : 1;
    }

    /** Normalisasi tanggal dari Excel: serial, Y-m-d, d/m/y (konvensi ID), Ymd -> 'Y-m-d' atau ''. */
    public static function parseDate(mixed $v): string
    {
        $v = trim((string) $v);
        if ($v === '') {
            return '';
        }
        if (ctype_digit($v) && (int) $v >= 20000 && (int) $v <= 80000) {
            return Date::excelToDateTimeObject((int) $v)->format('Y-m-d');
        }
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $v, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }
        if (preg_match('#^(\d{1,2})[/.\-](\d{1,2})[/.\-](\d{4})$#', $v, $m)) {
            $a = (int) $m[1];
            $b = (int) $m[2];
            // default hari/bulan/tahun (konvensi ID); bila komponen kedua > 12 berarti m/d/Y
            $d = $b > 12 ? $b : $a;
            $mo = $b > 12 ? $a : $b;
            return sprintf('%04d-%02d-%02d', (int) $m[3], $mo, $d);
        }
        if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $v, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }
        $t = strtotime($v);
        return $t === false ? '' : date('Y-m-d', $t);
    }

    /** Sel ke string: float bulat -> tanpa ".0" (no. HP/kode), RichText -> plain text. */
    private static function cellToString(mixed $v): string
    {
        if ($v === null) {
            return '';
        }
        if (is_int($v)) {
            return (string) $v;
        }
        if (is_float($v)) {
            return floor($v) === $v ? (string) (int) $v : rtrim(rtrim((string) $v, '0'), '.');
        }
        if (is_object($v)) {
            if (method_exists($v, 'getPlainText')) {
                return trim($v->getPlainText());
            }
            return method_exists($v, '__toString') ? trim((string) $v) : '';
        }
        return trim((string) $v);
    }

    private static function keyNorm(string $s): string
    {
        return preg_replace('/[^a-z0-9]/', '', mb_strtolower(trim($s), 'UTF-8')) ?? '';
    }

    private static function build(array $headers, array $rows, string $sheetTitle, array $petunjuk): Spreadsheet
    {
        $ss = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle(mb_substr($sheetTitle, 0, 31));
        $sheet->fromArray([$headers], null, 'A1');
        if ($rows !== []) {
            $sheet->fromArray($rows, null, 'A2');
        }
        if ($headers !== []) {
            $n = count($headers);
            $last = Coordinate::stringFromColumnIndex($n);
            $style = $sheet->getStyle('A1:' . $last . '1');
            $style->getFont()->setBold(true);
            $style->getFont()->getColor()->setRGB('FFFFFF');
            $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2E3450');
            $sheet->freezePane('A2');
            for ($c = 1; $c <= $n; $c++) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
            }
        }
        if ($petunjuk !== []) {
            $j = $ss->createSheet();
            $j->setTitle('Petunjuk');
            $j->fromArray($petunjuk, null, 'A1');
            $j->getColumnDimension('A')->setWidth(110);
            $j->getStyle('A1')->getFont()->setBold(true);
        }
        return $ss;
    }

    /** Validasi upload umum ($_FILES['file_excel']). @return string|null pesan error */
    public static function cekUpload(): ?string
    {
        $f = $_FILES['file_excel'] ?? null;
        if (!is_array($f) || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return 'File tidak terkirim. Pilih file .xlsx terlebih dulu.';
        }
        if (($f['size'] ?? 0) > 5 * 1024 * 1024) {
            return 'Ukuran file maksimal 5 MB.';
        }
        $ext = strtolower(pathinfo((string) ($f['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls'], true)) {
            return 'Format harus .xlsx atau .xls.';
        }
        return null;
    }

    /** Batas aman jumlah baris impor. */
    public static function maxImportRows(): int
    {
        return self::MAX_IMPORT_ROWS;
    }
}
