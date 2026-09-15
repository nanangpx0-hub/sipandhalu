<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';
$p = App\Core\Database::connection();
foreach ($p->query('SELECT id,label,jenis,status FROM periode ORDER BY id') as $r) {
    echo $r['id'], ' | ', $r['label'], ' | ', $r['jenis'], ' | ', $r['status'], PHP_EOL;
}
