<?php
declare(strict_types=1);
require 'vendor/autoload.php';
App\Core\Config::loadEnv(__DIR__);
$pdo = App\Core\Database::connection();

$rutaRepo = new App\Repositories\SampelRutaRepository($pdo);
$audit = new App\Repositories\AuditRepository($pdo);
$svc = new App\Services\MonitoringService(
    $pdo,
    new App\Repositories\DashboardRepository($pdo),
    new App\Repositories\PeriodeRepository($pdo),
    $rutaRepo,
    $audit,
    new App\Services\PengolahanService($pdo, $rutaRepo, $audit)
);

$f = $svc->normalizeFilters([]);
$f['periode_id'] = $svc->resolvePeriodeId(0, (new App\Repositories\PeriodeRepository($pdo))->all());
echo 'Periode terpilih: ' . $f['periode_id'] . PHP_EOL;
$p = $svc->buildPayload($f, null, ['id' => 1, 'role_code' => 'ADMIN']);

echo 'KPI cards: ' . count($p['kpi']) . PHP_EOL;
foreach ($p['kpi'] as $c) {
    echo '  - ' . $c['label'] . ' = ' . $c['value_display'] . ' (' . $c['pct_display'] . ', ' . $c['tone_label'] . ')' . PHP_EOL;
}
echo 'Total filtered: ' . $p['meta']['total_filtered'] . PHP_EOL;
$c = $p['consistency'];
echo 'Konsistensi: ' . $c['jumlah_ok'] . '/' . $c['jumlah_periksa'] . ' OK' . PHP_EOL;
$g = $svc->grid($f);
echo 'Grid: ' . count($g['rows']) . ' baris, total ' . $g['total'] . ', hal ' . $g['page'] . '/' . $g['pages'] . PHP_EOL;
$r = $g['rows'][0] ?? null;
if ($r) {
    echo 'Baris pertama: NKS ' . $r['nks'] . ' ' . $r['ruta_label'] . ' stage=' . $r['stage']['label'] . ' anomali=' . $r['anomali_label'] . PHP_EOL;
}
$d = $svc->detail($r['id'] ?? 1, ['id' => 1, 'role_code' => 'ADMIN']);
echo 'Drawer detail: ' . $d['row']['nks'] . ' ' . $d['row']['ruta_label'] . ', riwayat audit=' . count($d['riwayat']) . PHP_EOL;
echo 'Export rows: ';
[$h, $rows] = $svc->exportRows($f);
echo count($rows) . ' baris x ' . count($h) . ' kolom' . PHP_EOL;
