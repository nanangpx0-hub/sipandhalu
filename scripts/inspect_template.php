<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/../data/template_dokkirimkab _15092026.xls';
if (!file_exists($file)) {
    echo "File not found: $file\n";
    exit(1);
}

echo "Loading file: $file\n";
$spreadsheet = IOFactory::load($file);

$sheetCount = $spreadsheet->getSheetCount();
echo "Total sheets: $sheetCount\n";

foreach ($spreadsheet->getSheetNames() as $index => $name) {
    echo "--- Sheet {$index}: {$name} ---\n";
    $sheet = $spreadsheet->getSheet($index);
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    echo "Dimension: A1:{$highestColumn}{$highestRow}\n";

    // Print first 20 rows
    $limitRow = min($highestRow, 25);
    for ($row = 1; $row <= $limitRow; $row++) {
        $rowData = [];
        $colIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        $limitCol = min($colIndex, 35);
        for ($col = 1; $col <= $limitCol; $col++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $val = $sheet->getCell($colLetter . $row)->getFormattedValue();
            if ($val !== null && $val !== '') {
                $rowData[] = "{$colLetter}{$row}: '{$val}'";
            }
        }
        if (!empty($rowData)) {
            echo "Row {$row}: " . implode(' | ', $rowData) . "\n";
        }
    }
}
