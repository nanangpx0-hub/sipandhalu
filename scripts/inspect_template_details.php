<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/../data/template_dokkirimkab _15092026.xls';
$spreadsheet = IOFactory::load($file);

$sheet = $spreadsheet->getSheetByName('data');
$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();
$colIndexMax = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

echo "Checking data sheet columns with content in Row 1-3:\n";
for ($c = 1; $c <= $colIndexMax; $c++) {
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
    $v1 = $sheet->getCell($colLetter . '1')->getValue();
    $v2 = $sheet->getCell($colLetter . '2')->getValue();
    $v3 = $sheet->getCell($colLetter . '3')->getValue();
    if ($v1 || $v2 || $v3) {
        echo "Col {$colLetter} ({$c}): Row1='{$v1}' | Row2='{$v2}' | Row3='{$v3}'\n";
    }
}

echo "\nSummary of rows:\n";
$nksList = [];
$totalRowsWithData = 0;
for ($r = 4; $r <= $highestRow; $r++) {
    $prop = $sheet->getCell('A' . $r)->getValue();
    $kab = $sheet->getCell('B' . $r)->getValue();
    $nks = $sheet->getCell('C' . $r)->getValue();
    $ruta = $sheet->getCell('D' . $r)->getValue();
    $selesai = $sheet->getCell('E' . $r)->getValue();
    $modul = $sheet->getCell('F' . $r)->getValue();
    $kp = $sheet->getCell('G' . $r)->getValue();
    $tgl = $sheet->getCell('H' . $r)->getValue();
    $ttd_sos = $sheet->getCell('I' . $r)->getValue();
    $ttd_ipds = $sheet->getCell('J' . $r)->getValue();

    if ($prop !== null || $kab !== null || $nks !== null) {
        $totalRowsWithData++;
        if ($nks) {
            $nksList[$nks] = ($nksList[$nks] ?? 0) + 1;
        }
        // if some status is filled, let's see
        if ($selesai || $modul || $kp || $tgl || $ttd_sos || $ttd_ipds) {
            echo "Row {$r} filled: NKS={$nks}, Ruta={$ruta}, Selesai={$selesai}, Modul={$modul}, KP={$kp}, Tgl={$tgl}, Sos={$ttd_sos}, IPDS={$ttd_ipds}\n";
        }
    }
}

echo "Total data rows: {$totalRowsWithData}\n";
echo "Distinct NKS count: " . count($nksList) . "\n";
echo "NKS List: " . implode(', ', array_keys($nksList)) . "\n";

