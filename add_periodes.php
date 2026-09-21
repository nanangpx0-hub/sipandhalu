<?php
$cfg = require 'config/database.php';
$pdo = new PDO(
    sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']),
    $cfg['user'], $cfg['pass'], $cfg['options']
);

// Add missing Seruti periods Q2 and Q4
$periodes = [
    ['2026', 'SERUTI_Q2', '2026-Q2 Seruti Triwulan II', '2026-04-01', '2026-06-30', 'DRAFT', 'Master wilayah & penugasan Seruti Triwulan II 2026 (28 NKS)'],
    ['2026', 'SERUTI_Q4', '2026-Q4 Seruti Triwulan IV', '2026-10-01', '2026-12-31', 'DRAFT', 'Master wilayah & penugasan Seruti Triwulan IV 2026 (28 NKS)'],
];
foreach ($periodes as $p) {
    $stmt = $pdo->prepare("INSERT INTO periode (tahun, jenis, label, tgl_mulai, tgl_selesai, status, catatan) VALUES (?, ?, ?, ?, ?, ?, ?)");
    try {
        $stmt->execute($p);
        echo "Inserted: $p[1]\n";
    } catch (\Throwable $e) {
        echo "Skipped $p[1]: " . $e->getMessage() . "\n";
    }
}

// Verify
$stmt = $pdo->query("SELECT * FROM periode WHERE jenis LIKE 'SERUTI%' ORDER BY jenis");
echo "Seruti periods:\n";
foreach ($stmt->fetchAll() as $r) {
    echo "  id={$r['id']} jenis={$r['jenis']} status={$r['status']}\n";
}
