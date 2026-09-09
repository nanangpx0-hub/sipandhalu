<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\AuditRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;

final class AuthController
{
    private function svc(): AuthService
    {
        $pdo = Database::connection();
        return new AuthService($pdo, new UserRepository($pdo), new AuditRepository($pdo));
    }

    public function showLogin(Request $req, array $params = []): void
    {
        Session::start();
        if (!empty($_SESSION['user'])) {
            Response::redirect('/');
        }
        Response::view('auth/login.phtml', [
            'error' => Session::flash('error'),
            'csrf' => Csrf::field(),
            'old' => Session::flash('old') ?? [],
        ]);
    }

    public function login(Request $req, array $params = []): void
    {
        Session::start();
        $email = (string) ($req->post['email'] ?? '');
        $pass = (string) ($req->post['password'] ?? '');
        $res = $this->svc()->attempt($email, $pass, $req->ip(), $req->userAgent());
        if (!$res['ok']) {
            Session::flash('error', $res['error']);
            Session::flash('old', ['email' => $email]);
            Response::redirect('/login');
        }
        Session::regenerate();
        $_SESSION['user'] = $res['user'];
        if ((int) ($res['user']['must_reset'] ?? 0) === 1) {
            Session::flash('warning', 'Demi keamanan, segera ganti password bawaan Anda.');
            Response::redirect('/password');
        }
        Response::redirect('/');
    }

    public function logout(Request $req, array $params = []): void
    {
        Session::start();
        $pdo = Database::connection();
        $uid = $_SESSION['user']['id'] ?? null;
        if ($uid !== null) {
            (new AuditRepository($pdo))->log((int) $uid, 'LOGOUT', 'users', (string) $uid, null, null, $req->ip(), $req->userAgent());
        }
        Session::destroy();
        Session::start();
        Session::flash('success', 'Anda sudah keluar.');
        Response::redirect('/login');
    }

    public function showPassword(Request $req, array $params = []): void
    {
        Session::start();
        Response::view('auth/password.phtml', [
            'error' => Session::flash('error'),
            'success' => Session::flash('success'),
            'csrf' => Csrf::field(),
        ]);
    }

    public function updatePassword(Request $req, array $params = []): void
    {
        Session::start();
        $uid = (int) ($_SESSION['user']['id'] ?? 0);
        $res = $this->svc()->changeOwnPassword(
            $uid,
            (string) ($req->post['old_password'] ?? ''),
            (string) ($req->post['new_password'] ?? '')
        );
        if (!$res['ok']) {
            Session::flash('error', $res['error']);
            Response::redirect('/password');
        }
        $_SESSION['user']['must_reset'] = 0;
        Session::flash('success', 'Password berhasil diganti.');
        Response::redirect('/');
    }
}
