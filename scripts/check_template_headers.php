<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/../data/template_dokkirimkab _15092026.xls';
if (file_exists($file)) {
    $sp = IOFactory::load($file);
    echo "Sheets in template_dokkirimkab: " . implode(', ', $sp->getSheetNames()) . "\n";
    $sh = $sp->getSheet(0);
    echo "Row 1..3 headers:\n";
    for ($r = 1; $r <= 3; $r++) {
        $cells = [];
        for ($c = 1; $c <= 12; $c++) {
            $val = $sh->getCell([\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c) . $r])->getValue();
            if ($val !== null && $val !== '') $cells[] = "Col $c: $val";
        }
        echo "R$r: " . implode(' | ', $cells) . "\n";
    }
}
