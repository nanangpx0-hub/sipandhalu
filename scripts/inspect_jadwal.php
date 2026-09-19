<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$fileLk2 = __DIR__ . '/../data/LK Pengolahan Sampel (2).xlsx';
if (file_exists($fileLk2)) {
    echo "=== INSPECT LK Pengolahan Sampel (2).xlsx ===\n";
    $ss = IOFactory::load($fileLk2);
    foreach ($ss->getSheetNames() as $sheetName) {
        echo "Sheet: {$sheetName}\n";
    }
    
    $sheet = $ss->getSheetByName('Jadwal Pengawas Pengolahan');
    if ($sheet) {
        echo "\n--- Content of 'Jadwal Pengawas Pengolahan' in LK (2) ---\n";
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();
        echo "Dimension: A1:{$highestCol}{$highestRow}\n";
        for ($r = 1; $r <= min($highestRow, 50); $r++) {
            $rowVals = [];
            for ($c = 'A'; $c <= $highestCol; $c++) {
                $val = $sheet->getCell($c . $r)->getValue();
                if ($val !== null && trim((string)$val) !== '') {
                    $rowVals[] = "{$c}: " . trim((string)$val);
                }
            }
            if (!empty($rowVals)) {
                echo "Row {$r} -> " . implode(' | ', $rowVals) . "\n";
            }
        }
    }
}

$filePetugas = __DIR__ . '/../data/petugas.xlsx';
if (file_exists($filePetugas)) {
    echo "\n=== INSPECT petugas.xlsx ===\n";
    $ss2 = IOFactory::load($filePetugas);
    foreach ($ss2->getSheetNames() as $sheetName) {
        echo "Sheet: {$sheetName}\n";
        if (stripos($sheetName, 'jadwal') !== false || stripos($sheetName, 'pengawas') !== false) {
            $sh = $ss2->getSheetByName($sheetName);
            for ($r = 1; $r <= min($sh->getHighestRow(), 30); $r++) {
                $rowVals = [];
                for ($c = 'A'; $c <= $sh->getHighestColumn(); $c++) {
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
    }
}

$fileLk1 = __DIR__ . '/../data/LK Pengolahan Sampel (1).xlsx';
if (file_exists($fileLk1)) {
    echo "\n=== INSPECT LK Pengolahan Sampel (1).xlsx ===\n";
    $ss1 = IOFactory::load($fileLk1);
    foreach ($ss1->getSheetNames() as $sheetName) {
        echo "Sheet: {$sheetName}\n";
    }
    $sheet1 = $ss1->getSheetByName('Jadwal Pengawas Pengolahan');
    if ($sheet1) {
        echo "\n--- Content of 'Jadwal Pengawas Pengolahan' in LK (1) ---\n";
        for ($r = 1; $r <= min($sheet1->getHighestRow(), 50); $r++) {
            $rowVals = [];
            for ($c = 'A'; $c <= $sheet1->getHighestColumn(); $c++) {
                $val = $sheet1->getCell($c . $r)->getValue();
                if ($val !== null && trim((string)$val) !== '') {
                    $rowVals[] = "{$c}: " . trim((string)$val);
                }
            }
            if (!empty($rowVals)) {
                echo "Row {$r} -> " . implode(' | ', $rowVals) . "\n";
            }
        }
    }
}
