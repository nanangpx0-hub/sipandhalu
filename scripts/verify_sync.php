<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$pdo = Database::connection();

echo "=== 1. MASTER ORANG (AKTIF) BY ROLE ===\n";
$stmt = $pdo->query("
    SELECT r.code, r.label, COUNT(o.id) as jml
    FROM orang o
    LEFT JOIN roles r ON r.id = o.role_id
    WHERE o.is_aktif = 1
    GROUP BY r.code, r.label
    ORDER BY r.id
");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== 2. MASTER SLS WITH DESA & KECAMATAN ===\n";
$stmt = $pdo->query("
    SELECT COUNT(*) as total_sls,
           SUM(CASE WHEN s.desa_id IS NOT NULL THEN 1 ELSE 0 END) as with_desa_id,
           SUM(CASE WHEN d.nama IS NOT NULL THEN 1 ELSE 0 END) as with_desa_nama,
           SUM(CASE WHEN k.nama IS NOT NULL THEN 1 ELSE 0 END) as with_kec_nama
    FROM sls s
    LEFT JOIN desa d ON d.id = s.desa_id
    LEFT JOIN kecamatan k ON k.kode = d.kecamatan_kode
");
print_r($stmt->fetch(PDO::FETCH_ASSOC));

echo "\n=== 3. DAFTAR PERIODE ===\n";
$stmt = $pdo->query("
    SELECT p.id, p.tahun, p.jenis, p.label, p.status,
           COUNT(DISTINCT sp.id) as jml_sampel,
           COUNT(DISTINCT sr.id) as jml_ruta
    FROM periode p
    LEFT JOIN sampel sp ON sp.periode_id = p.id
    LEFT JOIN sampel_ruta sr ON sr.sampel_id = sp.id
    GROUP BY p.id, p.tahun, p.jenis, p.label, p.status
    ORDER BY p.tahun DESC, p.id ASC
");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== 4. SAMPEL & PENUGASAN SUSENAS S2 (ID 1) SAMPLE 5 ROWS ===\n";
$stmt = $pdo->query("
    SELECT s.nks, k.nama as kec, d.nama as desa,
           o_pcl.nama as pcl, o_pcl.no_hp as pcl_hp,
           o_pml.nama as pml, o_pml.no_hp as pml_hp,
           o_peng.nama as pengolah, o_peng.no_hp as pengolah_hp
    FROM sampel sp
    JOIN sls s ON s.id = sp.sls_id
    LEFT JOIN desa d ON d.id = s.desa_id
    LEFT JOIN kecamatan k ON k.kode = d.kecamatan_kode
    JOIN penugasan pg ON pg.sampel_id = sp.id
    JOIN orang o_pcl ON o_pcl.id = pg.pcl_id
    JOIN orang o_pml ON o_pml.id = pg.pml_id
    JOIN orang o_peng ON o_peng.id = pg.pengolah_id
    WHERE sp.periode_id = 1
    ORDER BY s.nks ASC
    LIMIT 5
");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== 5. SAMPEL & PENUGASAN SERUTI Q3 SAMPLE 5 ROWS ===\n";
$perQ3Id = (int)$pdo->query("SELECT id FROM periode WHERE jenis='SERUTI_Q3'")->fetchColumn();
$stmt = $pdo->query("
    SELECT s.nks, k.nama as kec, d.nama as desa,
           o_pcl.nama as pcl, o_pcl.no_hp as pcl_hp,
           o_pml.nama as pml, o_pml.no_hp as pml_hp,
           o_peng.nama as pengolah, o_peng.no_hp as pengolah_hp,
           COUNT(sr.id) as total_ruta
    FROM sampel sp
    JOIN sls s ON s.id = sp.sls_id
    LEFT JOIN desa d ON d.id = s.desa_id
    LEFT JOIN kecamatan k ON k.kode = d.kecamatan_kode
    JOIN penugasan pg ON pg.sampel_id = sp.id
    JOIN orang o_pcl ON o_pcl.id = pg.pcl_id
    JOIN orang o_pml ON o_pml.id = pg.pml_id
    JOIN orang o_peng ON o_peng.id = pg.pengolah_id
    LEFT JOIN sampel_ruta sr ON sr.sampel_id = sp.id
    WHERE sp.periode_id = {$perQ3Id}
    GROUP BY s.nks, k.nama, d.nama, o_pcl.nama, o_pcl.no_hp, o_pml.nama, o_pml.no_hp, o_peng.nama, o_peng.no_hp
    ORDER BY s.nks ASC
    LIMIT 5
");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
