<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\AuditRepository;
use App\Repositories\PeriodeRepository;
use App\Repositories\SampelRepository;
use App\Repositories\SlsRepository;
use App\Services\PenugasanService;

final class PeriodeController
{
    private PeriodeRepository $per;
    private SampelRepository $sampel;
    private SlsRepository $sls;

    public function __construct()
    {
        $pdo = Database::connection();
        $this->per = new PeriodeRepository($pdo);
        $this->sampel = new SampelRepository($pdo);
        $this->sls = new SlsRepository($pdo);
    }

    private function actor(): ?int
    {
        Session::start();
        return isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
    }

    public function index(Request $req, array $params = []): void
    {
        Response::view('periode/index.phtml', [
            'rows' => $this->per->all(),
            'success' => Session::flash('success'), 'error' => Session::flash('error'),
        ]);
    }

    public function create(Request $req, array $params = []): void
    {
        Response::view('periode/form.phtml', [
            'row' => Session::flash('old') ?? [], 'errors' => Session::flash('errors') ?? [],
            'csrf' => \App\Core\Csrf::field(),
        ]);
    }

    public function store(Request $req, array $params = []): void
    {
        $tahun = (int) ($req->post['tahun'] ?? 0);
        $jenis = trim((string) ($req->post['jenis'] ?? ''));
        $e = [];
        if ($tahun < 2020 || $tahun > 2035) {
            $e['tahun'] = 'Tahun 2020-2035.';
        }
        $valid = ['SERUTI_Q1','SERUTI_Q2','SERUTI_Q3','SERUTI_Q4','SUSENAS_S1','SUSENAS_S2'];
        if (!in_array($jenis, $valid, true)) {
            $e['jenis'] = 'Jenis tidak valid.';
        }
        if ($this->per->findByTahunJenis($tahun, $jenis) !== null) {
            $e['jenis'] = 'Periode ini sudah ada.';
        }
        if ($e !== []) {
            Session::flash('errors', $e);
            Session::flash('old', $req->post);
            Response::redirect('/periode/baru');
        }
        $label = trim((string) ($req->post['label'] ?? '')) ?: ($tahun . ' ' . str_replace('_', ' ', $jenis));
        $id = $this->per->create([
            'tahun' => $tahun, 'jenis' => $jenis, 'label' => $label,
            'tgl_mulai' => trim((string) ($req->post['tgl_mulai'] ?? '')),
            'tgl_selesai' => trim((string) ($req->post['tgl_selesai'] ?? '')),
            'status' => 'DRAFT', 'catatan' => trim((string) ($req->post['catatan'] ?? '')),
        ]);
        Session::flash('success', 'Periode dibuat (DRAFT).');
        Response::redirect('/periode/' . $id);
    }

    public function show(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $row = $this->per->find($id);
        if ($row === null) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.phtml';
            exit;
        }
        $q = (string) ($req->get['q'] ?? '');
        $page = max(1, (int) ($req->get['page'] ?? 1));
        $res = $this->sampel->paginateByPeriode($id, $q, $page, 15);
        Response::view('periode/show.phtml', [
            'row' => $row, 'ringkas' => $this->per->ringkasan($id),
            'beban' => $this->per->bebanPengolah($id),
            'sampel' => $res['data'], 'total' => $res['total'],
            'q' => $q, 'page' => $page, 'perPage' => 15,
            'success' => Session::flash('success'), 'error' => Session::flash('error'),
            'csrf' => \App\Core\Csrf::field(),
        ]);
    }

    public function setStatus(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $to = trim((string) ($req->post['status'] ?? ''));
        $row = $this->per->find($id);
        if ($row === null || !in_array($to, ['DRAFT','AKTIF','TUTUP'], true)) {
            Session::flash('error', 'Status tidak valid.');
            Response::redirect('/periode/' . $id);
        }
        $alir = ['DRAFT' => ['AKTIF'], 'AKTIF' => ['TUTUP'], 'TUTUP' => []];
        if (!in_array($to, $alir[$row['status']] ?? [], true)) {
            Session::flash('error', 'Alur status: DRAFT -> AKTIF -> TUTUP.');
            Response::redirect('/periode/' . $id);
        }
        $this->per->setStatus($id, $to);
        (new AuditRepository(Database::connection()))->log(
            $this->actor(), 'UPDATE', 'periode', (string) $id,
            ['status' => $row['status']], ['status' => $to], $req->ip(), $req->userAgent()
        );
        Session::flash('success', 'Status periode -> ' . $to);
        Response::redirect('/periode/' . $id);
    }

    public function addSampel(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $row = $this->per->find($id);
        if ($row === null || $row['status'] === 'TUTUP') {
            Session::flash('error', 'Periode TUTUP / tidak ada.');
            Response::redirect('/periode/' . $id);
        }
        $nks = str_pad(trim((string) ($req->post['nks'] ?? '')), 5, '0', STR_PAD_LEFT);
        $sls = $this->sls->findByNks($nks);
        if ($sls === null) {
            Session::flash('error', 'NKS tidak ada di master SLS. Tambahkan di /sls dulu.');
            Response::redirect('/periode/' . $id);
        }
        try {
            $this->sampel->add($id, (int) $sls['id'], 10, null);
            Session::flash('success', 'SLS ' . $sls['nks'] . ' masuk sampel (target 10).');
        } catch (\Throwable $e) {
            Session::flash('error', str_contains($e->getMessage(), 'Duplicate') ? 'SLS sudah ada di periode ini.' : $e->getMessage());
        }
        Response::redirect('/periode/' . $id);
    }

    public function assign(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $pdo = Database::connection();
        $svc = new PenugasanService($pdo, $this->sampel, new AuditRepository($pdo));
        $res = $svc->assign(
            $id, (int) ($req->post['sampel_id'] ?? 0),
            (int) ($req->post['pcl_id'] ?? 0), (int) ($req->post['pml_id'] ?? 0),
            (int) ($req->post['pengolah_id'] ?? 0),
            $this->actor(), $req->ip(), $req->userAgent()
        );
        Session::flash($res['ok'] ? 'success' : 'error', $res['ok'] ? 'Penugasan disimpan.' : implode(' ', $res['errors']));
        Response::redirect('/periode/' . $id);
    }
}
