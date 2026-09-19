<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use PhpOffice\PhpSpreadsheet\IOFactory;

$db = Database::connection();
$file = __DIR__ . '/../data/petugas.xlsx';
$sp = IOFactory::load($file);
$sheet = $sp->getSheet(0);

$pcls = [];
$pmls = [];
$pengolahs = [];
$slsList = [];

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

    $pcls[$pclNama] = $pclHp;
    $pmls[$pmlNama] = $pmlHp;
    $pengolahs[$pengolahNama] = $pengolahHp;

    $slsList[$nks] = [
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

echo "Total Unique PCL: " . count($pcls) . "\n";
echo "Total Unique PML: " . count($pmls) . "\n";
echo "Total Unique Pengolah: " . count($pengolahs) . "\n";
echo "Total SLS / NKS: " . count($slsList) . "\n\n";

// Compare with existing orang table
echo "=== PENGOLAH MATCHING ===\n";
foreach ($pengolahs as $nama => $hp) {
    $row = $db->query("SELECT id, nama, no_hp FROM orang WHERE nama LIKE '%" . explode(' ', $nama)[0] . "%'")->fetch(PDO::FETCH_ASSOC);
    echo "- Excel: '{$nama}' (HP: {$hp}) => DB: " . ($row ? "ID {$row['id']}: '{$row['nama']}'" : "NOT FOUND") . "\n";
}

echo "\n=== PML MATCHING ===\n";
foreach ($pmls as $nama => $hp) {
    $row = $db->query("SELECT id, nama, no_hp FROM orang WHERE nama LIKE '%" . explode(' ', $nama)[0] . "%'")->fetch(PDO::FETCH_ASSOC);
    echo "- Excel: '{$nama}' (HP: {$hp}) => DB: " . ($row ? "ID {$row['id']}: '{$row['nama']}'" : "NOT FOUND") . "\n";
}

echo "\n=== PCL MATCHING ===\n";
foreach ($pcls as $nama => $hp) {
    $row = $db->query("SELECT id, nama, no_hp FROM orang WHERE nama LIKE '%" . explode(' ', $nama)[0] . "%'")->fetch(PDO::FETCH_ASSOC);
    echo "- Excel: '{$nama}' (HP: {$hp}) => DB: " . ($row ? "ID {$row['id']}: '{$row['nama']}'" : "NOT FOUND") . "\n";
}

echo "\n=== SLS MATCHING ===\n";
foreach ($slsList as $nks => $d) {
    $row = $db->query("SELECT s.id, s.nks, s.nama_sls, d.nama as desa, k.nama as kec FROM sls s JOIN desa d ON d.id=s.desa_id JOIN kecamatan k ON k.kode=d.kecamatan_kode WHERE s.nks='{$nks}'")->fetch(PDO::FETCH_ASSOC);
    echo "- NKS {$nks}: Excel [{$d['kec_nama']} / {$d['desa_nama']}] => DB: " . ($row ? "ID {$row['id']}: '{$row['nama_sls']}' [{$row['kec']} / {$row['desa']}]" : "NOT FOUND") . "\n";
}
