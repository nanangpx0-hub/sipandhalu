<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$fileLk2 = __DIR__ . '/../data/LK Pengolahan Sampel (2).xlsx';
$ss = IOFactory::load($fileLk2);

foreach (['Sheet7', 'Master', 'Jadwal Pengawas Pengolahan'] as $shName) {
    $sh = $ss->getSheetByName($shName);
    if (!$sh) {
        echo "Sheet {$shName} not found.\n";
        continue;
    }
    echo "\n=== SHEET: {$shName} (Dimension: {$sh->calculateWorksheetDimension()}) ===\n";
    $hRow = $sh->getHighestRow();
    $hCol = $sh->getHighestColumn();
    for ($r = 1; $r <= min($hRow, 30); $r++) {
        $rowVals = [];
        for ($c = 'A'; $c <= min($hCol, 'Z'); $c++) {
            $val = $sh->getCell($c . $r)->getValue();
            if ($val !== null && trim((string)$val) !== '') {
                $rowVals[] = "{$c}: " . trim((string)$val);
            }
        }
        if (!empty($rowVals)) {
            echo "Row {$r} -> " . implode(' | ', $rowVals) . "\n";
        }
    }
}
