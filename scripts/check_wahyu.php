<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

\App\Core\Config::loadEnv(dirname(__DIR__));

$pdo = \App\Core\Database::connection();

$stmt = $pdo->query('SELECT * FROM orang WHERE id = 141');
print_r($stmt->fetch(PDO::FETCH_ASSOC));

$stmtPenugasan = $pdo->query('SELECT * FROM penugasan WHERE pcl_id = 141 OR pml_id = 141 OR pengolah_id = 141');
print_r($stmtPenugasan->fetchAll(PDO::FETCH_ASSOC));
