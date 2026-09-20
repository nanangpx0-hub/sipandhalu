<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Excel;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\AuditRepository;
use App\Repositories\DashboardRepository;
use App\Repositories\PeriodeRepository;
use App\Repositories\SampelRutaRepository;
use App\Services\MonitoringService;
use App\Services\PengolahanService;
use Throwable;

/**
 * Dashboard Monitoring Enterprise — controller tipis (tanpa SQL).
 *
 * Menyajikan:
 *  - GET  /monitoring             : halaman penuh (server-rendered agar FCP cepat)
 *  - GET  /monitoring/data        : JSON payload KPI+chart+grid (AJAX)
 *  - GET  /monitoring/options     : JSON opsi filter cascading
 *  - GET  /monitoring/detail/{id} : JSON detail + jejak audit (drawer)
 *  - POST /monitoring/quick-verify: aksi cepat + audit trail (CSRF)
 *  - GET  /monitoring/export      : unduh CSV / XLSX sesuai filter aktif
 */
final class MonitoringController
{
    private MonitoringService $svc;
    private PeriodeRepository $periodeRepo;

    public function __construct()
    {
        $pdo = Database::connection();
        $this->periodeRepo = new PeriodeRepository($pdo);
        $rutaRepo = new SampelRutaRepository($pdo);
        $audit = new AuditRepository($pdo);

        $this->svc = new MonitoringService(
            $pdo,
            new DashboardRepository($pdo),
            $this->periodeRepo,
            $rutaRepo,
            $audit,
            new PengolahanService($pdo, $rutaRepo, $audit)
        );
    }

    /** @return array<string,mixed> */
    private function currentUser(): array
    {
        Session::start();

        return $_SESSION['user'] ?? [];
    }

    /**
     * Ambil filter dari query string lalu normalisasi + resolusi periode.
     *
     * @return array<string,mixed>
     */
    private function filters(Request $req): array
    {
        $f = $this->svc->normalizeFilters($req->get);
        $f['periode_id'] = $this->svc->resolvePeriodeId((int) $f['periode_id'], $this->periodeRepo->all());

        return $f;
    }

    /** GET /monitoring — halaman dashboard (Tier 1-3) server-rendered. */
    public function index(Request $req, array $params = []): void
    {
        $filters = $this->filters($req);
        $periode = $filters['periode_id'] > 0 ? $this->periodeRepo->find((int) $filters['periode_id']) : null;

        Response::view('monitoring/index.phtml', [
            'title' => 'Monitoring Operasional',
            'periodes' => $this->periodeRepo->all(),
            'filters' => $filters,
            'periode' => $periode,
            'payload' => $this->svc->buildPayload($filters, $periode, $this->currentUser()),
            'grid' => $this->svc->grid($filters),
            'options' => $this->svc->options((int) $filters['periode_id']),
            'canQuick' => $this->svc->canQuickVerify($this->currentUser()),
            'csrf' => \App\Core\Csrf::field(),
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
        ]);
    }

    /** GET /monitoring/data — JSON payload + grid sesuai filter aktif. */
    public function data(Request $req, array $params = []): void
    {
        try {
            $filters = $this->filters($req);
            $periode = $filters['periode_id'] > 0 ? $this->periodeRepo->find((int) $filters['periode_id']) : null;

            Response::json([
                'ok' => true,
                'payload' => $this->svc->buildPayload($filters, $periode, $this->currentUser()),
                'grid' => $this->svc->grid($filters),
                'filters' => $filters,
                'server_time' => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $t) {
            Response::json(['ok' => false, 'message' => 'Gagal memuat data: ' . $t->getMessage()], 500);
        }
    }

    /** GET /monitoring/options — opsi cascading untuk periode terpilih. */
    public function options(Request $req, array $params = []): void
    {
        $periodeId = (int) ($req->get['periode_id'] ?? 0);
        if ($periodeId <= 0) {
            $periodeId = $this->svc->resolvePeriodeId(0, $this->periodeRepo->all());
        }

        Response::json(['ok' => true, 'options' => $this->svc->options($periodeId)]);
    }

    /** GET /monitoring/detail/{id} — detail + riwayat audit untuk drawer. */
    public function detail(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        try {
            Response::json(['ok' => true, 'detail' => $this->svc->detail($id, $this->currentUser())]);
        } catch (Throwable $t) {
            Response::json(['ok' => false, 'message' => $t->getMessage()], 404);
        }
    }

    /** POST /monitoring/quick-verify — aksi cepat status (CSRF + audit trail). */
    public function quickVerify(Request $req, array $params = []): void
    {
        $id = (int) ($req->post['ruta_id'] ?? 0);

        $payload = [];
        foreach (['status_dokumen', 'status_transfer_k', 'status_transfer_kp', 'status_transfer_seruti'] as $field) {
            if (!array_key_exists($field, $req->post)) {
                continue;
            }
            $v = $req->post[$field];
            if (is_string($v) && trim($v) === '') {
                continue;
            }
            if ($v === null) {
                continue;
            }
            $payload[$field] = $v;
        }

        try {
            $result = $this->svc->quickUpdate(
                $id,
                $payload,
                $this->currentUser(),
                $req->ip(),
                $req->userAgent()
            );

            Response::json([
                'ok' => true,
                'message' => 'Status tersimpan dan tercatat pada audit trail.',
                'result' => $result,
                'detail' => $this->svc->detail($id, $this->currentUser()),
            ]);
        } catch (Throwable $t) {
            $status = $t->getCode() === PengolahanService::HTTP_FORBIDDEN ? 403 : 400;
            Response::json(['ok' => false, 'message' => $t->getMessage()], $status);
        }
    }

    /** POST /monitoring/bulk-verify — aksi massal transfer untuk baris terpilih. */
    public function bulkVerify(Request $req, array $params = []): void
    {
        $field = trim((string) ($req->post['field'] ?? ''));
        $value = !empty($req->post['value']);
        $periodeId = (int) ($req->post['periode_id'] ?? 0);
        $raw = $req->post['ruta_ids'] ?? [];
        $ids = is_array($raw) ? $raw : [];

        try {
            $result = $this->svc->bulkTransfer(
                $ids,
                $field,
                $value,
                $periodeId,
                $this->currentUser(),
                $req->ip(),
                $req->userAgent()
            );

            Response::json([
                'ok' => true,
                'message' => sprintf('Aksi massal selesai: %d dari %d baris diperbarui dan tercatat pada audit trail.', $result['terupdate'], $result['diminta']),
                'result' => $result,
            ]);
        } catch (Throwable $t) {
            $status = $t->getCode() === PengolahanService::HTTP_FORBIDDEN ? 403 : 400;
            Response::json(['ok' => false, 'message' => $t->getMessage()], $status);
        }
    }

    /** GET /monitoring/export?format=csv|xlsx — unduh sesuai filter aktif. */
    public function export(Request $req, array $params = []): void
    {
        $filters = $this->filters($req);
        $format = strtolower(trim((string) ($req->get['format'] ?? 'csv')));

        try {
            [$headers, $rows] = $this->svc->exportRows($filters);
        } catch (Throwable $t) {
            Session::flash('error', 'Ekspor gagal: ' . $t->getMessage());
            Response::redirect('/monitoring?periode_id=' . (int) $filters['periode_id']);

            return;
        }

        $periode = $filters['periode_id'] > 0 ? $this->periodeRepo->find((int) $filters['periode_id']) : null;
        $label = preg_replace('/[^A-Za-z0-9]+/', '_', (string) ($periode['label'] ?? 'periode')) ?: 'periode';
        $stamp = date('Ymd_His');

        if ($format === 'xlsx') {
            Excel::download('monitoring_' . $label . '_' . $stamp . '.xlsx', $headers, $rows, 'Monitoring');

            return;
        }

        if (ob_get_level() > 0) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="monitoring_' . $label . '_' . $stamp . '.csv"');
        header('Cache-Control: max-age=0');

        $out = fopen('php://output', 'wb');
        if ($out === false) {
            exit;
        }
        // BOM agar Excel membaca UTF-8 dengan benar.
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers, ';');
        foreach ($rows as $row) {
            fputcsv($out, array_map([$this, 'safeCsvCell'], $row), ';');
        }
        fclose($out);
        exit;
    }

    /** Cegah CSV formula injection (=, +, -, @) saat dibuka di Excel. */
    private function safeCsvCell(mixed $v): string
    {
        $s = (string) $v;
        if ($s !== '' && preg_match('/^[=+\-@\t\r]/', $s) === 1) {
            return "'" . $s;
        }

        return $s;
    }
}