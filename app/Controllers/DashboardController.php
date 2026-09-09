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
        // ringkasan tahap 1: orang + users (CTE). Rekap beban per periode menyusul tahap 2.
        $ringkas = $pdo->query(
            "WITH o AS (SELECT COUNT(*) AS t, SUM(is_aktif=1) AS aktif FROM orang),
              u AS (SELECT COUNT(*) AS t, SUM(is_aktif=1) AS aktif FROM users)
             SELECT (SELECT t FROM o) AS orang_total, (SELECT aktif FROM o) AS orang_aktif,
                    (SELECT t FROM u) AS user_total, (SELECT aktif FROM u) AS user_aktif"
        )->fetch();
        $audit = (new AuditRepository($pdo))->paginate(1, 10);
        Response::view('dashboard/index.phtml', [
            'user' => $_SESSION['user'] ?? null,
            'ringkas' => $ringkas,
            'logs' => $audit['data'],
            'success' => Session::flash('success'),
        ]);
    }
}
