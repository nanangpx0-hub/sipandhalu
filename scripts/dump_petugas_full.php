<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use PhpOffice\PhpSpreadsheet\IOFactory;

$db = Database::connection();
$file = __DIR__ . '/../data/petugas.xlsx';
$sp = IOFactory::load($file);
$sheet = $sp->getSheetByName('Alokasi');

echo "Row count: " . $sheet->getHighestRow() . "\n";
$rows = [];
for ($r = 2; $r <= 29; $r++) {
    $nks = trim((string)$sheet->getCell([1, $r])->getValue());
    $kecKode = trim((string)$sheet->getCell([2, $r])->getValue());
    $kecNama = trim((string)$sheet->getCell([3, $r])->getValue());
    $desaKode = trim((string)$sheet->getCell([4, $r])->getValue());
    $desaNama = trim((string)$sheet->getCell([5, $r])->getValue());
    
    $pclNama = trim((string)$sheet->getCell([6, $r])->getValue());
    $pclHp = trim((string)$sheet->getCell([7, $r])->getValue());
    
    $pmlNama = trim((string)$sheet->getCell([8, $r])->getValue());
    $pmlHp = trim((string)$sheet->getCell([9, $r])->getValue());
    
    $pengolahNama = trim((string)$sheet->getCell([10, $r])->getValue());
    $pengolahHp = trim((string)$sheet->getCell([11, $r])->getValue());

    $rows[] = [
        'row' => $r,
        'nks' => $nks,
        'kec_kode' => $kecKode,
        'kec_nama' => $kecNama,
        'desa_kode' => $desaKode,
        'desa_nama' => $desaNama,
        'pcl' => $pclNama,
        'pcl_hp' => $pclHp,
        'pml' => $pmlNama,
        'pml_hp' => $pmlHp,
        'pengolah' => $pengolahNama,
        'pengolah_hp' => $pengolahHp,
    ];
}

foreach ($rows as $item) {
    printf(
        "%-5s | %-3s %-12s | %-3s %-15s | PCL: %-22s (%-12s) | PML: %-22s (%-12s) | Peng: %-22s (%-12s)\n",
        $item['nks'],
        $item['kec_kode'], substr($item['kec_nama'], 0, 12),
        $item['desa_kode'], substr($item['desa_nama'], 0, 15),
        substr($item['pcl'], 0, 22), $item['pcl_hp'],
        substr($item['pml'], 0, 22), $item['pml_hp'],
        substr($item['pengolah'], 0, 22), $item['pengolah_hp']
    );
}
