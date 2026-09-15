<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use App\Core\Database;

try {
    $db = Database::connection();
    echo "Connected successfully to DB.\n";
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(', ', $tables) . "\n\n";

    foreach ($tables as $t) {
        $count = $db->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
        echo "- {$t}: {$count} rows\n";
    }

    // Let's check periode
    if (in_array('periode', $tables)) {
        echo "\nPeriode data:\n";
        $periodes = $db->query("SELECT * FROM periode")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($periodes as $p) {
            echo "ID: {$p['id']} | {$p['label']} | {$p['jenis']} | {$p['status']}\n";
        }
    }

    // Let's check sampel count per periode
    if (in_array('sampel', $tables)) {
        echo "\nSampel counts per periode:\n";
        $stmt = $db->query("SELECT periode_id, COUNT(*) as cnt FROM sampel GROUP BY periode_id");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "Periode {$row['periode_id']}: {$row['cnt']} sampel\n";
        }
    }

} catch (\Exception $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
