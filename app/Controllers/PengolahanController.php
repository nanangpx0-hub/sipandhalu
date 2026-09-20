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

    /**
     * Petakan kode exception → status HTTP. RuntimeException ber-kode 403 dari
     * PengolahanService (penolakan RBAC) menjadi HTTP 403, sisanya 400.
     */
    private function denyCode(\Throwable $t): int
    {
        return $t->getCode() === PengolahanService::HTTP_FORBIDDEN ? 403 : 400;
    }

    /** Penolakan RBAC untuk endpoint JSON (AJAX). */
    private function denyJson(): void
    {
        Response::json(
            ['success' => false, 'message' => PengolahanService::EDIT_DENIED_MESSAGE],
            PengolahanService::HTTP_FORBIDDEN
        );
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

        // PENGOLAH: halaman otomatis menampilkan data binaan sendiri (parameter URL diabaikan).
        $isPengolahScope = (($user['role_code'] ?? $user['role'] ?? '') === 'PENGOLAH');
        $ownPengolahId = $isPengolahScope ? (int) ($user['orang_id'] ?? 0) : 0;

        $filters = [
            'pengolah_id' => !empty($req->get['pengolah_id']) ? (int) $req->get['pengolah_id'] : null,
            'status_dokumen' => $req->get['status_dokumen'] ?? null,
            'has_error' => $req->get['has_error'] ?? null,
            'q' => $req->get['q'] ?? null,
        ];
        if ($isPengolahScope) {
            $filters['pengolah_id'] = $ownPengolahId > 0 ? $ownPengolahId : -1;
        }

        // Daftar pengolah untuk dropdown filter (PENGOLAH: hanya dirinya)
        $pengolahList = [];
        if (!($isPengolahScope && $ownPengolahId <= 0)) {
            $sqlPengolah = 'SELECT DISTINCT o.id, o.nama
                 FROM penugasan pg
                 JOIN sampel sp ON sp.id = pg.sampel_id
                 JOIN orang o ON o.id = pg.pengolah_id
                 WHERE sp.periode_id = :p';
            $paramPengolah = [':p' => $periodeId];
            if ($isPengolahScope) {
                $sqlPengolah .= ' AND pg.pengolah_id = :own';
                $paramPengolah[':own'] = $ownPengolahId;
            }
            $stmtPengolah = $this->pdo->prepare($sqlPengolah . ' ORDER BY o.nama ASC');
            $stmtPengolah->execute($paramPengolah);
            $pengolahList = $stmtPengolah->fetchAll(PDO::FETCH_ASSOC);
        }

        $rutaList = $this->pengolahanSvc->getDaftarRuta($periodeId, $filters, $user);
        $summary = $this->pengolahanSvc->getSummary($periodeId);
        if ($isPengolahScope && $ownPengolahId > 0) {
            // Tabel beban: hanya baris milik sendiri.
            $summary['beban_pengolah'] = array_values(array_filter(
                $summary['beban_pengolah'] ?? [],
                static fn ($bp): bool => (int) ($bp['pengolah_id'] ?? 0) === $ownPengolahId
            ));
        }
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
            'isPengolahScope' => $isPengolahScope,
            'currentUser' => $user,
            // RBAC: true hanya untuk ADMIN, OPERATOR, SM_PLS (Tim IPDS).
            'canEdit' => $this->pengolahanSvc->canEdit($user),
            'csrf' => \App\Core\Csrf::field(),
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
        ]);
    }

    /** POST /pengolahan/update-ruta — AJAX endpoint simpan nilai status/keterangan ruta. */
    public function updateRuta(Request $req, array $params = []): void
    {
        $user = $this->currentUser();
        if (!$this->pengolahanSvc->canEdit($user)) {
            $this->denyJson();
            return;
        }

        $rutaId = (int) $req->input('ruta_id');
        if ($rutaId <= 0) {
            Response::json(['success' => false, 'message' => 'ID ruta tidak valid.'], 400);
            return;
        }

        $data = [];
        $fields = [
            'status_dokumen', 'status_transfer_k', 'status_transfer_kp', 'status_transfer_seruti',
            'status_selesai', 'catatan_kp', 'ket_kp_pengolah', 'ket_kp_lapangan', 'ket_kp_sosial', 'ket_kp_ipds',
            'catatan_modul', 'ket_m_pengolah', 'ket_m_lapangan', 'ket_m_sosial', 'ket_m_ipds', 'uji_petik_pengawas',
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
            Response::json(['success' => false, 'message' => $t->getMessage()], $this->denyCode($t));
        }
    }

    /** POST /pengolahan/batch-transfer — AJAX endpoint transfer serentak K/KP per NKS atau pilihan ruta. */
    public function batchTransfer(Request $req, array $params = []): void
    {
        $user = $this->currentUser();
        if (!$this->pengolahanSvc->canEdit($user)) {
            $this->denyJson();
            return;
        }

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
            Response::json(['success' => false, 'message' => $e->getMessage()], $this->denyCode($e));
        }
    }

    /** POST /pengolahan/terima-dokumen — AJAX endpoint penerimaan dokumen fisik (1 SLS atau pilihan ruta). */
    public function terimaDokumen(Request $req, array $params = []): void
    {
        $user = $this->currentUser();
        if (!$this->pengolahanSvc->canEdit($user)) {
            $this->denyJson();
            return;
        }

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
            Response::json(['success' => false, 'message' => $e->getMessage()], $this->denyCode($e));
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

        // RBAC: impor LK = mutasi massal → hanya ADMIN, OPERATOR, SM_PLS (Tim IPDS).
        if (!$this->pengolahanSvc->canEdit($user)) {
            $isAjax = strtolower((string) ($req->server['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
            if ($isAjax) {
                $this->denyJson();
                return;
            }
            Response::view('errors/403.phtml', [
                'pesan' => PengolahanService::EDIT_DENIED_MESSAGE,
            ], PengolahanService::HTTP_FORBIDDEN);

            return;
        }

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
