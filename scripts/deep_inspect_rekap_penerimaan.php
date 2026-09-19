<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/../data/Rekap_Penerimaan_Dokumen.xlsx';
$sp = IOFactory::load($file);
$sheet = $sp->getSheet(0);

echo "Headers (Row 1):\n";
for ($c = 1; $c <= 25; $c++) {
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
    $val = $sheet->getCell([$c, 1])->getValue();
    if ($val !== null && $val !== '') {
        echo "Col {$colLetter} ({$c}): '{$val}'\n";
    }
}

$statusCounts = [];
$nonEmptyRows = 0;
$rowsWithStatus = 0;
$distinctNks = [];
$sampleData = [];

for ($r = 2; $r <= $sheet->getHighestRow(); $r++) {
    $no = $sheet->getCell('A' . $r)->getValue();
    $nks = $sheet->getCell('B' . $r)->getValue();
    $kec = $sheet->getCell('C' . $r)->getValue();
    $desa = $sheet->getCell('D' . $r)->getValue();
    $ruta = $sheet->getCell('E' . $r)->getValue();
    $status = $sheet->getCell('F' . $r)->getValue();

    if ($no === null && $nks === null) {
        continue;
    }
    $nonEmptyRows++;
    if ($nks) {
        $distinctNks[(string)$nks] = true;
    }

    // Check if any other columns G to U have data
    $extraCols = [];
    for ($c = 7; $c <= 21; $c++) {
        $v = $sheet->getCell([$c, $r])->getValue();
        if ($v !== null && trim((string)$v) !== '') {
            $extraCols[$c] = $v;
        }
    }

    $stStr = trim((string)$status);
    $statusCounts[$stStr] = ($statusCounts[$stStr] ?? 0) + 1;
    if ($stStr !== '') {
        $rowsWithStatus++;
        if (count($sampleData) < 15) {
            $sampleData[] = [
                'row' => $r,
                'no' => $no,
                'nks' => $nks,
                'kec' => $kec,
                'desa' => $desa,
                'ruta' => $ruta,
                'status' => $stStr,
                'extra' => $extraCols,
            ];
        }
    }
}

echo "\nTotal data rows: {$nonEmptyRows}\n";
echo "Total distinct NKS: " . count($distinctNks) . "\n";
echo "Distinct NKS list: " . implode(', ', array_keys($distinctNks)) . "\n";
echo "Status counts:\n";
print_r($statusCounts);

echo "\nSample rows with status:\n";
print_r($sampleData);
