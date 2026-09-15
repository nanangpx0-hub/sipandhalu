<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use App\Core\Database;
use PhpOffice\PhpSpreadsheet\IOFactory;

$db = Database::connection();

// NKS from DB SLS
$dbSls = $db->query("SELECT id, nks, kec, desa, sls, sub, nama_sls, dusun, rw, rt FROM sls ORDER BY nks")->fetchAll(PDO::FETCH_ASSOC);
$dbNksMap = [];
foreach ($dbSls as $s) {
    $dbNksMap[$s['nks']] = $s;
}

// NKS from Excel
$file = __DIR__ . '/../data/template_dokkirimkab _15092026.xls';
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getSheetByName('data');
$highestRow = $sheet->getHighestRow();

$excelNks = [];
for ($r = 4; $r <= $highestRow; $r++) {
    $nks = (string)$sheet->getCell('C' . $r)->getValue();
    if ($nks !== '') {
        $nks = str_pad(trim($nks), 5, '0', STR_PAD_LEFT);
        $excelNks[$nks] = ($excelNks[$nks] ?? 0) + 1;
    }
}

echo "DB NKS count: " . count($dbNksMap) . "\n";
echo "Excel NKS count: " . count($excelNks) . "\n";

$dbKeys = array_keys($dbNksMap);
$excelKeys = array_keys($excelNks);

$diff1 = array_diff($excelKeys, $dbKeys);
$diff2 = array_diff($dbKeys, $excelKeys);

echo "NKS in Excel but not in DB: " . (empty($diff1) ? "NONE (ALL MATCH!)" : implode(', ', $diff1)) . "\n";
echo "NKS in DB but not in Excel: " . (empty($diff2) ? "NONE (ALL MATCH!)" : implode(', ', $diff2)) . "\n";

// Check sampel in Periode 1 (Susenas September 2026)
$sampelP1 = $db->query("
    SELECT s.id as sampel_id, s.nks, smp.id as smp_id, smp.target_sampel, 
           smp.dok_pemutakhiran_status, smp.peta_status, smp.dokumen_vsen, smp.peta_ws
    FROM sampel smp
    JOIN sls s ON smp.sls_id = s.id
    WHERE smp.periode_id = 1
    ORDER BY s.nks
")->fetchAll(PDO::FETCH_ASSOC);

echo "Periode 1 sampel count: " . count($sampelP1) . "\n";
