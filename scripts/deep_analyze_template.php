<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/../data/template_dokkirimkab _15092026.xls';
$spreadsheet = IOFactory::load($file);

foreach ($spreadsheet->getSheetNames() as $sName) {
    echo "================ SHEET: {$sName} ================\n";
    $sheet = $spreadsheet->getSheetByName($sName);
    $highestRow = $sheet->getHighestRow();
    $highestCol = $sheet->getHighestColumn();
    echo "Dimensions: A1:{$highestCol}{$highestRow}\n";

    // Show row 1 to 4 headers
    for ($r = 1; $r <= 4; $r++) {
        $rowStr = [];
        for ($c = 1; $c <= 12; $c++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $val = $sheet->getCell($colLetter . $r)->getValue();
            if ($val !== null && $val !== '') {
                $rowStr[] = "{$colLetter}{$r}=" . json_encode($val);
            }
        }
        echo "Row {$r}: " . implode(' | ', $rowStr) . "\n";
    }

    // Let's count non-empty values in cols E, F, G, H, I, J
    $filledE = 0; $filledF = 0; $filledG = 0; $filledH = 0; $filledI = 0; $filledJ = 0;
    $distinctDates = [];
    $modulVals = [];
    $kpVals = [];
    $selesaiVals = [];

    for ($r = 4; $r <= $highestRow; $r++) {
        $e = $sheet->getCell('E' . $r)->getValue();
        $f = $sheet->getCell('F' . $r)->getValue();
        $g = $sheet->getCell('G' . $r)->getValue();
        $h = $sheet->getCell('H' . $r)->getFormattedValue();
        $i = $sheet->getCell('I' . $r)->getValue();
        $j = $sheet->getCell('J' . $r)->getValue();

        if ($e !== null && $e !== '') { $filledE++; $selesaiVals[(string)$e] = ($selesaiVals[(string)$e] ?? 0) + 1; }
        if ($f !== null && $f !== '') { $filledF++; $modulVals[(string)$f] = ($modulVals[(string)$f] ?? 0) + 1; }
        if ($g !== null && $g !== '') { $filledG++; $kpVals[(string)$g] = ($kpVals[(string)$g] ?? 0) + 1; }
        if ($h !== null && $h !== '') { $filledH++; $distinctDates[$h] = ($distinctDates[$h] ?? 0) + 1; }
        if ($i !== null && $i !== '') $filledI++;
        if ($j !== null && $j !== '') $filledJ++;
    }

    echo "Stats (from row 4 to {$highestRow}):\n";
    echo "- Sudah Selesai (E): {$filledE} filled. Values: " . json_encode($selesaiVals) . "\n";
    echo "- Blok Modul (F): {$filledF} filled. Values: " . json_encode($modulVals) . "\n";
    echo "- Blok KP (G): {$filledG} filled. Values: " . json_encode($kpVals) . "\n";
    echo "- Tgl Pengiriman (H): {$filledH} filled. Dates: " . json_encode($distinctDates) . "\n";
    echo "- TTD Sos (I): {$filledI} filled.\n";
    echo "- TTD IPDS (J): {$filledJ} filled.\n";
}
