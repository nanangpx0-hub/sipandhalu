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
use App\Repositories\SampelRutaRepository;
use App\Services\PengolahanService;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDO;

final class PengolahanController
{
    private PeriodeRepository $periodeRepo;
    private SampelRutaRepository $rutaRepo;
    private PengolahanService $pengolahanSvc;
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
        $this->periodeRepo = new PeriodeRepository($this->pdo);
        $this->rutaRepo = new SampelRutaRepository($this->pdo);
        $this->pengolahanSvc = new PengolahanService(
            $this->pdo,
            $this->rutaRepo,
            new AuditRepository($this->pdo)
        );
    }

    private function currentUser(): array
    {
        Session::start();
        return $_SESSION['user'] ?? [];
    }

    /** GET /pengolahan — Tampilan utama lembar kerja & pemantauan pengolahan sampel. */
    public function index(Request $req, array $params = []): void
    {
        $user = $this->currentUser();
        $periodes = $this->periodeRepo->all();

        // Cari periode aktif atau yang dipilih di query
        $periodeId = (int) ($req->get['periode_id'] ?? $req->query('periode_id') ?? 0);
        if ($periodeId <= 0) {
            foreach ($periodes as $p) {
                if ($p['status'] === 'AKTIF') {
                    $periodeId = (int) $p['id'];
                    break;
                }
            }
            if ($periodeId <= 0 && $periodes !== []) {
                $periodeId = (int) $periodes[0]['id'];
            }
        }

        $filters = [
            'pengolah_id' => !empty($req->get['pengolah_id']) ? (int) $req->get['pengolah_id'] : null,
            'status_dokumen' => $req->get['status_dokumen'] ?? null,
            'has_error' => $req->get['has_error'] ?? null,
            'q' => $req->get['q'] ?? null,
        ];

        // Daftar pengolah untuk dropdown filter
        $stmtPengolah = $this->pdo->prepare(
            'SELECT DISTINCT o.id, o.nama
             FROM penugasan pg
             JOIN sampel sp ON sp.id = pg.sampel_id
             JOIN orang o ON o.id = pg.pengolah_id
             WHERE sp.periode_id = :p
             ORDER BY o.nama ASC'
        );
        $stmtPengolah->execute([':p' => $periodeId]);
        $pengolahList = $stmtPengolah->fetchAll(PDO::FETCH_ASSOC);

        $rutaList = $this->pengolahanSvc->getDaftarRuta($periodeId, $filters, $user);
        $summary = $this->pengolahanSvc->getSummary($periodeId);
        $jadwalPengawas = $this->pengolahanSvc->getJadwalPengawas($periodeId);
        $pengawasHariIni = $this->pengolahanSvc->getPengawasHariIni($periodeId);

        Response::view('pengolahan/index.phtml', [
            'periodes' => $periodes,
            'currentPeriodeId' => $periodeId,
            'pengolahList' => $pengolahList,
            'rutaList' => $rutaList,
            'summary' => $summary,
            'jadwalPengawas' => $jadwalPengawas,
            'pengawasHariIni' => $pengawasHariIni,
            'todayYmd' => date('Y-m-d'),
            'filters' => $filters,
            'currentUser' => $user,
            'csrf' => \App\Core\Csrf::field(),
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
        ]);
    }

    /** POST /pengolahan/update-ruta — AJAX endpoint simpan nilai status/keterangan ruta. */
    public function updateRuta(Request $req, array $params = []): void
    {
        $user = $this->currentUser();
        $rutaId = (int) $req->input('ruta_id');
        if ($rutaId <= 0) {
            Response::json(['success' => false, 'message' => 'ID ruta tidak valid.'], 400);
            return;
        }

        $data = [];
        $fields = [
            'status_dokumen', 'status_transfer_k', 'status_transfer_kp', 'status_transfer_seruti',
            'status_selesai', 'catatan_kp', 'ket_kp_pengolah', 'ket_kp_lapangan', 'ket_kp_sosial',
            'catatan_modul', 'ket_m_pengolah', 'ket_m_lapangan', 'ket_m_sosial', 'uji_petik_pengawas',
            'tgl_pengiriman', 'ttd_sos', 'ttd_ipds'
        ];

        foreach ($fields as $f) {
            if ($req->input($f) !== null) {
                $data[$f] = $req->input($f);
            }
        }

        try {
            $updated = $this->pengolahanSvc->updateRuta($rutaId, $data, $user);
            Response::json([
                'success' => true,
                'message' => 'Data pengolahan ruta berhasil disimpan.',
                'data' => $updated,
            ]);
        } catch (\Throwable $t) {
            Response::json(['success' => false, 'message' => $t->getMessage()], 400);
        }
    }

    /** POST /pengolahan/batch-transfer — AJAX endpoint transfer serentak K/KP per NKS atau pilihan ruta. */
    public function batchTransfer(Request $req, array $params = []): void
    {
        $user = $this->currentUser();
        $user['ip'] = $req->ip();
        $user['user_agent'] = $req->userAgent();

        $field = (string) ($req->post['field'] ?? $req->input('field') ?? '');
        $value = (int) ($req->post['value'] ?? $req->input('value') ?? 1);
        $periodeId = (int) ($req->post['periode_id'] ?? $req->input('periode_id') ?? 0);
        $nks = trim((string) ($req->post['nks'] ?? $req->input('nks') ?? ''));
        $rutaIds = $req->post['ruta_ids'] ?? [];

        try {
            $count = $this->pengolahanSvc->batchTransfer([
                'periode_id' => $periodeId,
                'nks' => $nks,
                'ruta_ids' => $rutaIds,
                'field' => $field,
                'value' => $value,
            ], $user);

            $labelField = match ($field) {
                'status_transfer_k' => 'Transfer K (Kor)',
                'status_transfer_kp' => 'Transfer KP',
                'status_transfer_seruti' => 'Transfer Seruti',
                default => 'Transfer'
            };

            Response::json([
                'success' => true,
                'message' => "Berhasil memperbarui {$labelField} sebanyak {$count} ruta.",
                'count' => $count
            ]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /** POST /pengolahan/terima-dokumen — AJAX endpoint penerimaan dokumen fisik (1 SLS atau pilihan ruta). */
    public function terimaDokumen(Request $req, array $params = []): void
    {
        $user = $this->currentUser();
        $user['ip'] = $req->ip();
        $user['user_agent'] = $req->userAgent();

        $periodeId = (int) ($req->post['periode_id'] ?? $req->input('periode_id') ?? 0);
        $nks = trim((string) ($req->post['nks'] ?? $req->input('nks') ?? ''));
        $rutaIds = $req->post['ruta_ids'] ?? [];
        $tglPengiriman = (string) ($req->post['tgl_pengiriman'] ?? $req->input('tgl_pengiriman') ?? '');
        $ttdSos = (string) ($req->post['ttd_sos'] ?? $req->input('ttd_sos') ?? '');
        $ttdIpds = (string) ($req->post['ttd_ipds'] ?? $req->input('ttd_ipds') ?? '');

        try {
            $count = $this->pengolahanSvc->batchTerimaDokumen([
                'periode_id' => $periodeId,
                'nks' => $nks,
                'ruta_ids' => $rutaIds,
                'tgl_pengiriman' => $tglPengiriman,
                'ttd_sos' => $ttdSos,
                'ttd_ipds' => $ttdIpds,
            ], $user);

            Response::json([
                'success' => true,
                'message' => "Berhasil mencatat penerimaan {$count} dokumen fisik.",
                'count' => $count
            ]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /** GET /pengolahan/export — Unduh file Excel LK Pengolahan multi-sheet. */
    public function exportExcel(Request $req, array $params = []): void
    {
        $periodeId = (int) ($req->get['periode_id'] ?? $req->query('periode_id') ?? 0);
        if ($periodeId <= 0) {
            Session::flash('error', 'Periode tidak valid.');
            Response::redirect('/pengolahan');
            return;
        }

        $periode = $this->periodeRepo->find($periodeId);
        $spreadsheet = $this->pengolahanSvc->exportLkExcel($periodeId);

        $filename = 'LK_Pengolahan_Sampel_' . preg_replace('/[^A-Za-z0-9]+/', '_', (string) ($periode['label'] ?? $periodeId)) . '_' . date('Ymd_His') . '.xlsx';

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

    /** POST /pengolahan/import — Unggah & sinkronisasi file Excel LK Pengolahan. */
    public function importExcel(Request $req, array $params = []): void
    {
        $user = $this->currentUser();
        $periodeId = (int) $req->input('periode_id');
        if ($periodeId <= 0) {
            Session::flash('error', 'Periode tidak valid.');
            Response::redirect('/pengolahan');
            return;
        }

        $err = Excel::cekUpload();
        if ($err !== null) {
            Session::flash('error', $err);
            Response::redirect('/pengolahan?periode_id=' . $periodeId);
            return;
        }

        $file = $_FILES['file_excel'];
        try {
            $res = $this->pengolahanSvc->importLkExcel($periodeId, (string) $file['tmp_name'], $user);
            Session::flash(
                'success',
                sprintf('Sinkronisasi LK Pengolahan berhasil: %d status rekap dan %d catatan pemeriksaan diperbarui.', $res['updated_rekap'], $res['updated_catatan'])
            );
        } catch (\Throwable $t) {
            Session::flash('error', 'Gagal impor file LK: ' . $t->getMessage());
        }

        Response::redirect('/pengolahan?periode_id=' . $periodeId);
    }
}
