<?php
require __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load(__DIR__ . '/../data/template_dokkirimkab _15092026.xls');
$sheet = $spreadsheet->getSheetByName('data');

for ($r = 280; $r <= 286; $r++) {
    echo "Row $r: A=" . var_export($sheet->getCell("A$r")->getValue(), true)
       . ", B=" . var_export($sheet->getCell("B$r")->getValue(), true)
       . ", C=" . var_export($sheet->getCell("C$r")->getValue(), true)
       . ", D=" . var_export($sheet->getCell("D$r")->getValue(), true)
       . ", E=" . var_export($sheet->getCell("E$r")->getValue(), true)
       . "\n";
}
