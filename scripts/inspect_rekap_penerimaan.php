<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/../data/Rekap_Penerimaan_Dokumen.xlsx';
if (!file_exists($file)) {
    echo "File {$file} does NOT exist!\n";
    // Check what files are in data/
    print_r(scandir(__DIR__ . '/../data'));
    exit(1);
}

echo "File exists! Size: " . filesize($file) . " bytes\n";
$reader = IOFactory::createReaderForFile($file);
$sheetNames = $reader->listWorksheetNames($file);
echo "Sheets:\n";
print_r($sheetNames);

$sp = IOFactory::load($file);
foreach ($sheetNames as $sName) {
    echo "========================================================\n";
    echo " Sheet: {$sName}\n";
    echo "========================================================\n";
    $sheet = $sp->getSheetByName($sName);
    $hRow = $sheet->getHighestRow();
    $hCol = $sheet->getHighestColumn();
    echo "Dimensions: A1:{$hCol}{$hRow}\n";

    for ($r = 1; $r <= min(20, $hRow); $r++) {
        $rowVals = [];
        $colMax = min(20, \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($hCol));
        for ($c = 1; $c <= $colMax; $c++) {
            $val = $sheet->getCell([$c, $r])->getFormattedValue();
            $rowVals[] = trim((string)$val);
        }
        echo "R{$r}: " . implode(' | ', $rowVals) . "\n";
    }
}
