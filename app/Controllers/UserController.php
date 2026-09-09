<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\AuditRepository;
use App\Repositories\OrangRepository;
use App\Repositories\UserRepository;
use App\Services\UserService;

final class UserController
{
    private UserService $svc;
    private UserRepository $repo;
    private OrangRepository $orang;

    public function __construct()
    {
        $pdo = Database::connection();
        $this->repo = new UserRepository($pdo);
        $this->orang = new OrangRepository($pdo);
        $this->svc = new UserService($pdo, $this->repo, new AuditRepository($pdo));
    }

    private function actor(): ?int
    {
        Session::start();
        return isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
    }

    public function index(Request $req, array $params = []): void
    {
        $q = (string) ($req->get['q'] ?? '');
        $role = (string) ($req->get['role'] ?? 'all');
        $status = (string) ($req->get['status'] ?? 'all');
        $page = max(1, (int) ($req->get['page'] ?? 1));
        $res = $this->repo->paginate($q, $page, 15, $role, $status);
        Response::view('users/index.phtml', [
            'rows' => $res['data'], 'total' => $res['total'],
            'roles' => $this->repo->roles(),
            'rekap' => $this->repo->countByRole(),
            'q' => $q, 'role' => $role, 'status' => $status, 'page' => $page, 'perPage' => 15,
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
        ]);
    }

    public function create(Request $req, array $params = []): void
    {
        Response::view('users/form.phtml', [
            'mode' => 'create',
            'row' => Session::flash('old') ?? [],
            'errors' => Session::flash('errors') ?? [],
            'roles' => $this->repo->roles(),
            'orangOptions' => $this->orang->options(),
            'csrf' => \App\Core\Csrf::field(),
        ]);
    }

    public function store(Request $req, array $params = []): void
    {
        $in = [
            'nama' => $req->post['nama'] ?? '',
            'email' => $req->post['email'] ?? '',
            'password' => $req->post['password'] ?? '',
            'role_id' => $req->post['role_id'] ?? '',
            'orang_id' => $req->post['orang_id'] ?? '',
            'is_aktif' => isset($req->post['is_aktif']) ? 1 : 0,
        ];
        $res = $this->svc->create($in, $this->actor(), $req->ip(), $req->userAgent());
        if (!$res['ok']) {
            Session::flash('errors', $res['errors']);
            Session::flash('old', $in);
            Response::redirect('/users/baru');
        }
        Session::flash('success', 'Akun berhasil dibuat.');
        Response::redirect('/users');
    }

    public function edit(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $row = $this->repo->find($id);
        if ($row === null) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.phtml';
            exit;
        }
        Response::view('users/form.phtml', [
            'mode' => 'edit', 'id' => $id,
            'row' => Session::flash('old') ?? $row,
            'errors' => Session::flash('errors') ?? [],
            'roles' => $this->repo->roles(),
            'orangOptions' => $this->orang->options(),
            'csrf' => \App\Core\Csrf::field(),
        ]);
    }

    public function update(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $in = [
            'nama' => $req->post['nama'] ?? '',
            'email' => $req->post['email'] ?? '',
            'password' => $req->post['password'] ?? '',
            'role_id' => $req->post['role_id'] ?? '',
            'orang_id' => $req->post['orang_id'] ?? '',
            'is_aktif' => isset($req->post['is_aktif']) ? 1 : 0,
        ];
        $res = $this->svc->update($id, $in, $this->actor(), $req->ip(), $req->userAgent());
        if (!$res['ok']) {
            Session::flash('errors', $res['errors']);
            Session::flash('old', $in);
            Response::redirect('/users/' . $id . '/edit');
        }
        Session::flash('success', 'Akun berhasil diperbarui.');
        Response::redirect('/users');
    }

    public function toggle(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $row = $this->repo->find($id);
        $self = $this->actor();
        if ($row === null) {
            Session::flash('error', 'Akun tidak ditemukan.');
            Response::redirect('/users');
        }
        $ok = $this->svc->setAktif($id, (int) $row['is_aktif'] !== 1, $self, $req->ip(), $req->userAgent(), $self);
        Session::flash($ok ? 'success' : 'error', $ok ? 'Status akun diperbarui.' : 'Tidak bisa menonaktifkan akun sendiri.');
        Response::redirect('/users');
    }

    public function reset(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $plain = $this->svc->resetPassword($id, $this->actor(), $req->ip(), $req->userAgent());
        if ($plain === null) {
            Session::flash('error', 'Akun tidak ditemukan.');
        } else {
            // tampilkan sekali saja; user wajib ganti saat login
            Session::flash('success', 'Password sementara: ' . $plain . ' — catat sekarang, tidak ditampilkan lagi.');
        }
        Response::redirect('/users');
    }
}
