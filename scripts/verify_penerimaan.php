<?php
require __DIR__ . '/../vendor/autoload.php';

$pdo = App\Core\Database::connection();

echo "=== SUMMARY STATUS DOKUMEN & SERAH TERIMA (PERIODE 1) ===\n";
$stmt = $pdo->query("
    SELECT sr.status_dokumen,
           COUNT(*) as total,
           SUM(CASE WHEN sr.tgl_pengiriman IS NOT NULL THEN 1 ELSE 0 END) as with_tgl,
           SUM(CASE WHEN sr.ttd_sos IS NOT NULL THEN 1 ELSE 0 END) as with_penyerah_sos,
           SUM(CASE WHEN sr.ttd_ipds IS NOT NULL THEN 1 ELSE 0 END) as with_penerima_ipds
    FROM sampel_ruta sr
    JOIN sampel sp ON sp.id = sr.sampel_id
    WHERE sp.periode_id = 1
    GROUP BY sr.status_dokumen
");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== CONTOH 5 RUTA BERSTATUS ADA ===\n";
$stmt2 = $pdo->query("
    SELECT s.nks, sr.no_urut_ruta, sr.status_dokumen, sr.tgl_pengiriman, sr.ttd_sos, sr.ttd_ipds
    FROM sampel_ruta sr
    JOIN sampel sp ON sp.id = sr.sampel_id
    JOIN sls s ON s.id = sp.sls_id
    WHERE sp.periode_id = 1 AND sr.status_dokumen = 'ADA'
    ORDER BY s.nks, sr.no_urut_ruta
    LIMIT 5
");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
