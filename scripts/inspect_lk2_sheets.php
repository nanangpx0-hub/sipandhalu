<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/../data/LK Pengolahan Sampel (2).xlsx';
$sp = IOFactory::load($file);

echo "Sheets in LK Pengolahan Sampel (2).xlsx:\n";
foreach ($sp->getSheetNames() as $name) {
    $sheet = $sp->getSheetByName($name);
    echo "Sheet: {$name}, Rows: " . $sheet->getHighestRow() . ", Cols: " . $sheet->getHighestColumn() . "\n";
    // Check row 1 or 2 headers
    $h = [];
    for ($c = 1; $c <= min(15, \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn())); $c++) {
        $v = $sheet->getCell([$c, 1])->getValue();
        if ($v) $h[] = $v;
    }
    echo "  Headers: " . implode(' | ', $h) . "\n";
}
