<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

\App\Core\Config::loadEnv(dirname(__DIR__));

use App\Core\Database;
use App\Repositories\AuditRepository;
use App\Repositories\SampelRutaRepository;
use App\Services\PengolahanService;

$pdo = Database::connection();
$rutaRepo = new SampelRutaRepository($pdo);
$svc = new PengolahanService($pdo, $rutaRepo, new AuditRepository($pdo));

$ss = $svc->exportLkExcel(1);
echo 'Sheets count: ' . count($ss->getSheetNames()) . PHP_EOL;
echo 'Sheets: ' . implode(', ', $ss->getSheetNames()) . PHP_EOL;

$sh = $ss->getSheetByName('Rekap');
echo 'Col G1: ' . $sh->getCell('G1')->getValue() . PHP_EOL;
echo 'Col H1: ' . $sh->getCell('H1')->getValue() . PHP_EOL;
echo 'Col I1: ' . $sh->getCell('I1')->getValue() . PHP_EOL;
echo 'Row 2 status: ' . $sh->getCell('F2')->getValue() . ' | tgl: ' . $sh->getCell('G2')->getValue() . ' | sos: ' . $sh->getCell('H2')->getValue() . ' | ipds: ' . $sh->getCell('I2')->getValue() . PHP_EOL;
echo 'Row 3 status: ' . $sh->getCell('F3')->getValue() . ' | tgl: ' . $sh->getCell('G3')->getValue() . ' | sos: ' . $sh->getCell('H3')->getValue() . ' | ipds: ' . $sh->getCell('I3')->getValue() . PHP_EOL;

