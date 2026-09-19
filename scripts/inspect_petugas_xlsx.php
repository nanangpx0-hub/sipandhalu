<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$file = __DIR__ . '/../data/petugas.xlsx';
if (!file_exists($file)) {
    echo "File not found: $file\n";
    exit(1);
}

echo "Loading file: $file\n";
$reader = IOFactory::createReaderForFile($file);
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($file);

$sheetNames = $spreadsheet->getSheetNames();
echo "Total sheets: " . count($sheetNames) . "\n";
echo "Sheets: " . implode(', ', $sheetNames) . "\n\n";

foreach ($sheetNames as $idx => $sName) {
    $sheet = $spreadsheet->getSheetByName($sName);
    $hRow = $sheet->getHighestRow();
    $hCol = $sheet->getHighestColumn();
    $hColIdx = Coordinate::columnIndexFromString($hCol);
    echo "=== Sheet [{$idx}] '{$sName}': Dimension A1:{$hCol}{$hRow} (Cols: {$hColIdx}, Rows: {$hRow}) ===\n";

    // Print top 10 rows
    for ($r = 1; $r <= min($hRow, 10); $r++) {
        $rowCells = [];
        for ($c = 1; $c <= min($hColIdx, 25); $c++) {
            $v = trim((string)$sheet->getCell([$c, $r])->getValue());
            if ($v !== '') {
                $colL = Coordinate::stringFromColumnIndex($c);
                $rowCells[] = "{$colL}{$r}: '{$v}'";
            }
        }
        if (!empty($rowCells)) {
            echo "Row {$r}: " . implode(' | ', $rowCells) . "\n";
        }
    }
    echo "\n";
}
