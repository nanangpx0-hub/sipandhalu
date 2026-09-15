<?php
declare(strict_types=1);
/**
 * Import data riil template Dok Kirim Kab ke database dev untuk satu periode.
 * Usage: php scripts/import_dok_kirim.php <periode_id> [path_excel]
 */
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Repositories\AuditRepository;
use App\Repositories\SampelRutaRepository;
use App\Services\DokumenKirimService;

$periodeId = (int) ($argv[1] ?? 0);
$file = $argv[2] ?? (__DIR__ . '/../data/template_dokkirimkab _15092026.xls');
if ($periodeId <= 0) {
    fwrite(STDERR, "Usage: php scripts/import_dok_kirim.php <periode_id> [path_excel]\n");
    exit(1);
}

$pdo = Database::connection();
$rutaRepo = new SampelRutaRepository($pdo);
$svc = new DokumenKirimService($pdo, $rutaRepo, new AuditRepository($pdo));

// Inisialisasi 10 ruta default untuk seluruh sampel periode
$stmt = $pdo->prepare('SELECT id FROM sampel WHERE periode_id = :p');
$stmt->execute([':p' => $periodeId]);
foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $sid) {
    $svc->initRutaForSampel((int) $sid);
}

$rekap = $svc->importExcel($periodeId, $file, 1, 'cli', 'import_dok_kirim.php');
printf(
    "Periode %d: total=%d terupdate=%d dilewati=%d errors=%d\n",
    $periodeId,
    $rekap['total'],
    $rekap['terupdate'],
    $rekap['dilewati'],
    count($rekap['errors'])
);
foreach (array_slice($rekap['errors'], 0, 10) as $e) {
    echo '  ERR: ', $e, PHP_EOL;
}
$st = $pdo->prepare(
    "SELECT status_selesai, COUNT(*) AS n FROM sampel_ruta sr
     JOIN sampel sp ON sp.id = sr.sampel_id WHERE sp.periode_id = :p GROUP BY status_selesai"
);
$st->execute([':p' => $periodeId]);
foreach ($st->fetchAll() as $r) {
    echo $r['status_selesai'], ': ', $r['n'], PHP_EOL;
}
