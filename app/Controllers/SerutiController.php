<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Excel;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\AuditRepository;
use App\Repositories\PeriodeRepository;
use App\Services\SerutiService;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Fill\Fill;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill as StyleFill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDO;
use RuntimeException;

final class SerutiController
{
    private PeriodeRepository $periodeRepo;
    private SerutiService $serutiSvc;
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
        $this->periodeRepo = new PeriodeRepository($this->pdo);
        $this->serutiSvc = new SerutiService(
            $this->pdo,
            new \App\Repositories\SampelRutaRepository($this->pdo),
            $this->periodeRepo,
            new AuditRepository($this->pdo)
        );
    }

    private function currentUser(): array
    {
        Session::start();
        return $_SESSION['user'] ?? [];
    }

    private function denyJson(): void
    {
        Response::json(
            ['success' => false, 'message' => 'Akses ditolak: Anda tidak memiliki wewenang.'],
            403
        );
    }

    /** GET /seruti — Halaman utama Pengolahan Seruti. */
    public function index(Request $req, array $params = []): void
    {
        $user = $this->currentUser();
        $role = (string) ($user['role_code'] ?? $user['role'] ?? 'VIEWER');

        $periodes = $this->serutiSvc->getSerutiPeriodes();
        $periodeId = (int) ($req->get['periode_id'] ?? $req->query('periode_id') ?? 0);
        if ($periodeId <= 0) {
            $periodeId = $this->serutiSvc->getDefaultSerutiPeriodeId();
        }
        if ($periodeId <= 0 && $periodes !== []) {
            $periodeId = (int) $periodes[0]['id'];
        }

        $pengolahScope = $this->serutiSvc->getPengolahScope($user);
        $canTransferSeruti = $this->serutiSvc->canTransferSeruti($user);
        $canEdit = $this->serutiSvc->canEdit($user);

        $filters = [
            'status_kesiapan' => $req->get['status_kesiapan'] ?? null,
            'pengolah_nama' => $req->get['pengolah_nama'] ?? null,
            'q' => $req->get['q'] ?? null,
        ];

        $rutaList = $this->serutiSvc->getDaftarRuta($periodeId, $filters, $user);
        $kpi = $this->serutiSvc->getKpi($periodeId, $pengolahScope > 0 ? $pengolahScope : -1);
        $nksCount = $this->serutiSvc->countNKS($periodeId, $pengolahScope > 0 ? $pengolahScope : -1);
        $pengolahList = $this->serutiSvc->getPengolahList($periodeId, $pengolahScope > 0 ? $pengolahScope : -1);
        $currentPeriode = $this->periodeRepo->findById($periodeId);

        Response::view('seruti/index.phtml', [
            'periodes' => $periodes,
            'currentPeriodeId' => $periodeId,
            'currentPeriode' => $currentPeriode,
            'rutaList' => $rutaList,
            'kpi' => $kpi,
            'nksCount' => $nksCount,
            'pengolahList' => $pengolahList,
            'filters' => $filters,
            'isPengolahScope' => $pengolahScope > 0,
            'currentUser' => $user,
            'canTransferSeruti' => $canTransferSeruti,
            'canEdit' => $canEdit,
            'csrf' => \App\Core\Csrf::field(),
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
        ]);
    }

    /** POST /seruti/update-ruta — AJAX endpoint simpan nilai status/keterangan ruta Seruti. */
    public function updateRuta(Request $req, array $params = []): void
    {
        $user = $this->currentUser();
        $user['ip'] = $req->ip();
        $user['user_agent'] = $req->userAgent();

        $rutaId = (int) $req->input('ruta_id');
        if ($rutaId <= 0) {
            Response::json(['success' => false, 'message' => 'ID ruta tidak valid.'], 400);
            return;
        }

        $data = [];
        $fields = [
            'status_transfer_seruti',
            'catatan_kp', 'ket_kp_pengolah', 'ket_kp_lapangan', 'ket_kp_sosial', 'ket_kp_ipds',
            'catatan_modul', 'ket_m_pengolah', 'ket_m_lapangan', 'ket_m_sosial', 'ket_m_ipds',
            'uji_petik_pengawas',
            'catatan_seruti',
        ];

        foreach ($fields as $f) {
            if ($req->input($f) !== null) {
                $data[$f] = $req->input($f);
            }
        }

        try {
            $updated = $this->serutiSvc->updateRuta($rutaId, $data, $user);
            Response::json([
                'success' => true,
                'message' => 'Data pengolahan Seruti ruta berhasil disimpan.',
                'data' => $updated,
            ]);
        } catch (\Throwable $t) {
            $code = $t->getCode() === SerutiService::HTTP_FORBIDDEN ? 403 : 400;
            Response::json(['success' => false, 'message' => $t->getMessage()], $code);
        }
    }

    /** POST /seruti/batch-transfer — AJAX endpoint transfer Seruti serentak. */
    public function batchTransfer(Request $req, array $params = []): void
    {
        $user = $this->currentUser();
        $user['ip'] = $req->ip();
        $user['user_agent'] = $req->userAgent();

        $periodeId = (int) ($req->post['periode_id'] ?? $req->input('periode_id') ?? 0);
        $nks = trim((string) ($req->post['nks'] ?? $req->input('nks') ?? ''));
        $rutaIds = $req->post['ruta_ids'] ?? [];
        $value = !empty($req->post['value']) ? 1 : 0;

        try {
            $count = $this->serutiSvc->batchTransfer([
                'periode_id' => $periodeId,
                'nks' => $nks,
                'ruta_ids' => $rutaIds,
                'value' => $value,
            ], $user);

            Response::json([
                'success' => true,
                'message' => "Berhasil memperbarui Transfer Seruti sebanyak {$count} ruta.",
                'count' => $count,
            ]);
        } catch (\Throwable $e) {
            $code = $e->getCode() === SerutiService::HTTP_FORBIDDEN ? 403 : 400;
            Response::json(['success' => false, 'message' => $e->getMessage()], $code);
        }
    }

    /** GET /seruti/export — Unduh file Excel rekap pengolahan Seruti. */
    public function export(Request $req, array $params = []): void
    {
        $user = $this->currentUser();
        $periodeId = (int) ($req->get['periode_id'] ?? $req->query('periode_id') ?? 0);
        if ($periodeId <= 0) {
            Session::flash('error', 'Periode tidak valid.');
            Response::redirect('/seruti');
            return;
        }

        $filters = [
            'status_kesiapan' => $req->get['status_kesiapan'] ?? null,
            'q' => $req->get['q'] ?? null,
        ];

        $exportData = $this->serutiSvc->exportData($periodeId, $filters, $user);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Seruti');

        $headers = $exportData['headers'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:' . Coordinate::stringFromColumnIndex(count($headers)) . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . Coordinate::stringFromColumnIndex(count($headers)) . '1')->getFill()
            ->setFillType(StyleFill::FILL_SOLID)->getStartColor()->setRGB('2E3450');
        $sheet->getStyle('A1:' . Coordinate::stringFromColumnIndex(count($headers)) . '1')->getFont()->setColor(new Color('FFFFFF'));

        $rowIdx = 2;
        foreach ($exportData['rows'] as $r) {
            $sheet->fromArray(array_values($r), null, 'A' . $rowIdx);
            $rowIdx++;
        }

        for ($ci = 1; $ci <= count($headers); $ci++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($ci))->setWidth(18);
        }
        $sheet->freezePane('A2');

        $filename = 'Seruti_Pengolahan_' . preg_replace('/[^A-Za-z0-9]+/', '_', (string) ($exportData['periodeLabel'] ?? $periodeId)) . '_' . date('Ymd_His') . '.xlsx';

        if (ob_get_level() > 0) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
