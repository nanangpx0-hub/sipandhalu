<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\AuditRepository;

final class DashboardController
{
    public function index(Request $req, array $params = []): void
    {
        Session::start();
        $pdo = Database::connection();
        $ringkas = $pdo->query(
            "WITH o AS (SELECT COUNT(*) AS t, SUM(is_aktif=1) AS aktif FROM orang),
              u AS (SELECT COUNT(*) AS t, SUM(is_aktif=1) AS aktif FROM users),
              s AS (SELECT COUNT(*) AS t, SUM(is_aktif=1) AS aktif FROM sls),
              p AS (SELECT COUNT(*) AS t, SUM(status='AKTIF') AS aktif,
                    SUM(status='DRAFT') AS draft, SUM(status='TUTUP') AS tutup FROM periode)
             SELECT (SELECT t FROM o) AS orang_total, (SELECT aktif FROM o) AS orang_aktif,
                    (SELECT t FROM u) AS user_total, (SELECT aktif FROM u) AS user_aktif,
                    (SELECT t FROM s) AS sls_total, (SELECT aktif FROM s) AS sls_aktif,
                    (SELECT t FROM p) AS periode_total, (SELECT aktif FROM p) AS periode_aktif,
                    (SELECT draft FROM p) AS periode_draft, (SELECT tutup FROM p) AS periode_tutup"
        )->fetch();
        $audit = (new AuditRepository($pdo))->paginate(1, 10);
        Response::view('dashboard/index.phtml', [
            'user' => $_SESSION['user'] ?? null,
            'ringkas' => $ringkas,
            'logs' => $audit['data'],
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
        ]);
    }
}
