<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::connection();
$orang = $db->query("SELECT id, nama, no_hp, role_id, is_aktif FROM orang ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
echo "Total orang in DB: " . count($orang) . "\n";
foreach ($orang as $o) {
    echo "ID {$o['id']}: {$o['nama']} | No HP: {$o['no_hp']} | Role ID: {$o['role_id']}\n";
}
