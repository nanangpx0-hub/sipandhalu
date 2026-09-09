<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\SlsRepository;
use App\Repositories\WilayahRepository;
use App\Repositories\DesaRepository;
use App\Repositories\AuditRepository;
use App\Services\SlsService;

final class SlsController
{
    private SlsRepository $sls;
    private SlsService $svc;
    private WilayahRepository $wil;
    private DesaRepository $desa;

    public function __construct()
    {
        $pdo = Database::connection();
        $this->sls = new SlsRepository($pdo);
        $this->wil = new WilayahRepository($pdo);
        $this->desa = new DesaRepository($pdo);
        $this->svc = new SlsService($pdo, $this->sls, $this->desa, $this->wil, new AuditRepository($pdo));
    }

    private function actor(): ?int
    {
        Session::start();
        return isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
    }

    public function index(Request $req, array $params = []): void
    {
        $q = (string) ($req->get['q'] ?? '');
        $kec = (string) ($req->get['kec'] ?? 'all');
        $page = max(1, (int) ($req->get['page'] ?? 1));
        $res = $this->sls->paginate($q, $page, 15, $kec);
        Response::view('sls/index.phtml', [
            'rows' => $res['data'], 'total' => $res['total'],
            'kecs' => $this->wil->allKecamatan(),
            'q' => $q, 'kec' => $kec, 'page' => $page, 'perPage' => 15,
            'success' => Session::flash('success'), 'error' => Session::flash('error'),
        ]);
    }

    public function create(Request $req, array $params = []): void
    {
        Response::view('sls/form.phtml', [
            'mode' => 'create', 'row' => Session::flash('old') ?? [],
            'errors' => Session::flash('errors') ?? [],
            'kecs' => $this->wil->allKecamatan(), 'csrf' => \App\Core\Csrf::field(),
        ]);
    }

    public function store(Request $req, array $params = []): void
    {
        $pdo = Database::connection();
        $this->svc = new SlsService($pdo, $this->sls, $this->desa, $this->wil, new AuditRepository($pdo));
        // create butuh transaksi+audit: delegasi manual agar file tetap kecil
        $n = $this->svc->normalize($req->post);
        if (!$n['ok']) {
            Session::flash('errors', $n['errors']);
            Session::flash('old', $req->post);
            Response::redirect('/sls/baru');
        }
        $pdo->beginTransaction();
        try {
            $id = $this->sls->create($n['data']);
            (new AuditRepository($pdo))->log($this->actor(), 'CREATE', 'sls', (string) $id, null, $this->sls->find($id), $req->ip(), $req->userAgent());
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        Session::flash('success', 'SLS berhasil ditambah.');
        Response::redirect('/sls');
    }

    public function edit(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $row = $this->sls->find($id);
        if ($row === null) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.phtml';
            exit;
        }
        Response::view('sls/form.phtml', [
            'mode' => 'edit', 'id' => $id, 'row' => Session::flash('old') ?? $row,
            'errors' => Session::flash('errors') ?? [],
            'kecs' => $this->wil->allKecamatan(), 'csrf' => \App\Core\Csrf::field(),
        ]);
    }

    public function update(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $pdo = Database::connection();
        $row = $this->sls->find($id);
        if ($row === null) {
            Session::flash('error', 'SLS tidak ditemukan.');
            Response::redirect('/sls');
        }
        $n = $this->svc->normalize($req->post, $id);
        if (!$n['ok']) {
            Session::flash('errors', $n['errors']);
            Session::flash('old', $req->post);
            Response::redirect('/sls/' . $id . '/edit');
        }
        $pdo->beginTransaction();
        try {
            $this->sls->update($id, $n['data']);
            (new AuditRepository($pdo))->log($this->actor(), 'UPDATE', 'sls', (string) $id, $row, $this->sls->find($id), $req->ip(), $req->userAgent());
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        Session::flash('success', 'SLS diperbarui.');
        Response::redirect('/sls');
    }
}
