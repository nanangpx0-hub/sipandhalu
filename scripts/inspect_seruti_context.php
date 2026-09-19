<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::connection();
echo "=== PERIODE SAAT INI ===\n";
$periodes = $db->query("SELECT * FROM periode")->fetchAll(PDO::FETCH_ASSOC);
print_r($periodes);

echo "=== SAMPEL DAN NKS PER PERIODE ===\n";
foreach ($periodes as $p) {
    $count = $db->query("SELECT COUNT(*) FROM sampel WHERE periode_id = {$p['id']}")->fetchColumn();
    $nks = $db->query("SELECT sl.nks FROM sampel sp JOIN sls sl ON sl.id = sp.sls_id WHERE sp.periode_id = {$p['id']}")->fetchAll(PDO::FETCH_COLUMN);
    echo "Periode {$p['id']} ({$p['label']} - {$p['jenis']}): {$count} sampel\n";
    echo "  NKS: " . implode(', ', $nks) . "\n";
}
