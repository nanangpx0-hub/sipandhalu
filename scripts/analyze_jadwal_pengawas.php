<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

\App\Core\Config::loadEnv(dirname(__DIR__));

$pdo = \App\Core\Database::connection();

$pengawasNames = [
    'Arumita Hertriesa',
    'Wahyu Wijayanti',
    'Nanang Pamungkas',
    'Qudrat Jufrian Bharata',
    'Silvie Kristya Ardearista',
];

echo "=== CHECK PENGAWAS PENGOLAHAN IN DB ===\n";
foreach ($pengawasNames as $name) {
    $stmt = $pdo->prepare('SELECT * FROM orang WHERE nama LIKE :n');
    $stmt->execute([':n' => '%' . $name . '%']);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) {
        echo "[-] {$name} NOT FOUND in table `orang`\n";
    } else {
        foreach ($rows as $r) {
            echo "[+] {$name} FOUND: ID {$r['id']}, Nama: {$r['nama']}\n";
        }
    }
}

echo "\n=== ALL ROLES IN DB ===\n";
$stmtRoles = $pdo->query('SELECT * FROM roles');
foreach ($stmtRoles->fetchAll(PDO::FETCH_ASSOC) as $role) {
    echo "Role ID {$role['id']}: {$role['code']} - {$role['label']}\n";
}

echo "\n=== DATABASE SCHEMA CHECK FOR JADWAL / PENGAWAS ===\n";
$stmtTables = $pdo->query('SHOW TABLES');
$tables = $stmtTables->fetchAll(PDO::FETCH_COLUMN);
echo "Tables: " . implode(', ', $tables) . "\n";

echo "\n=== CHECK USERS ASSIGNED TO THESE PENGAWAS ===\n";
$stmtUsers = $pdo->query('SELECT u.id, u.email, u.nama, r.code AS role_code, o.nama AS orang_nama FROM users u JOIN roles r ON r.id = u.role_id LEFT JOIN orang o ON o.id = u.orang_id');
foreach ($stmtUsers->fetchAll(PDO::FETCH_ASSOC) as $u) {
    echo "User {$u['id']}: {$u['email']} | {$u['nama']} | Role: {$u['role_code']} | Orang: {$u['orang_nama']}\n";
}
