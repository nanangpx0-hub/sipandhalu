<?php

declare(strict_types=1);

/** @var \App\Core\Router $router */
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\OrangController;
use App\Controllers\PeriodeController;
use App\Controllers\SlsController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

$router->get('/login', AuthController::class, 'showLogin');
$router->post('/login', AuthController::class, 'login', [CsrfMiddleware::class]);
$router->post('/logout', AuthController::class, 'logout', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/', DashboardController::class, 'index', [AuthMiddleware::class]);
$router->get('/password', AuthController::class, 'showPassword', [AuthMiddleware::class]);
$router->post('/password', AuthController::class, 'updatePassword', [AuthMiddleware::class, CsrfMiddleware::class]);

// Petugas (pool orang): semua user login boleh lihat; ubah dibatasi ADMIN+OPERATOR via cek di view+controller lanjutan.
// Tahap 1: batasi tulis ke ADMIN saja agar aman.
$router->get('/petugas', OrangController::class, 'index', [AuthMiddleware::class]);
$router->get('/petugas/baru', OrangController::class, 'create', [AuthMiddleware::class]);
$router->get('/petugas/export', OrangController::class, 'exportExcel', [AuthMiddleware::class]);
$router->get('/petugas/template', OrangController::class, 'templateExcel', [AuthMiddleware::class]);
$router->post('/petugas/import', OrangController::class, 'importExcel', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/petugas', OrangController::class, 'store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/petugas/{id}', OrangController::class, 'show', [AuthMiddleware::class]);
$router->get('/petugas/{id}/edit', OrangController::class, 'edit', [AuthMiddleware::class]);
$router->post('/petugas/{id}', OrangController::class, 'update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/petugas/{id}/toggle', OrangController::class, 'toggle', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/petugas/{id}/alias', OrangController::class, 'addAlias', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/petugas/{id}/alias/hapus', OrangController::class, 'deleteAlias', [AuthMiddleware::class, CsrfMiddleware::class]);

// Users: khusus ADMIN
$router->get('/users', UserController::class, 'index', [AuthMiddleware::class]);
$router->get('/users/baru', UserController::class, 'create', [AuthMiddleware::class]);
$router->post('/users', UserController::class, 'store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/users/{id}/edit', UserController::class, 'edit', [AuthMiddleware::class]);
$router->post('/users/{id}', UserController::class, 'update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/users/{id}/toggle', UserController::class, 'toggle', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/users/{id}/reset', UserController::class, 'reset', [AuthMiddleware::class, CsrfMiddleware::class]);

// Master SLS (1 SLS = 1 RT)
$router->get('/sls', SlsController::class, 'index', [AuthMiddleware::class]);
$router->get('/sls/baru', SlsController::class, 'create', [AuthMiddleware::class]);
$router->get('/sls/export', SlsController::class, 'exportExcel', [AuthMiddleware::class]);
$router->get('/sls/template', SlsController::class, 'templateExcel', [AuthMiddleware::class]);
$router->post('/sls/import', SlsController::class, 'importExcel', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/sls', SlsController::class, 'store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/sls/{id}/edit', SlsController::class, 'edit', [AuthMiddleware::class]);
$router->post('/sls/{id}', SlsController::class, 'update', [AuthMiddleware::class, CsrfMiddleware::class]);

// Periode + sampel + penugasan (level SLS)
$router->get('/periode', PeriodeController::class, 'index', [AuthMiddleware::class]);
$router->get('/periode/baru', PeriodeController::class, 'create', [AuthMiddleware::class]);
$router->get('/periode/export', PeriodeController::class, 'exportExcel', [AuthMiddleware::class]);
$router->get('/periode/{id}/template-sampel', PeriodeController::class, 'templateSampel', [AuthMiddleware::class]);
$router->post('/periode/{id}/import-sampel', PeriodeController::class, 'importSampel', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/periode', PeriodeController::class, 'store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/periode/{id}', PeriodeController::class, 'show', [AuthMiddleware::class]);
$router->post('/periode/{id}/status', PeriodeController::class, 'setStatus', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/periode/{id}/sampel', PeriodeController::class, 'addSampel', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/periode/{id}/assign', PeriodeController::class, 'assign', [AuthMiddleware::class, CsrfMiddleware::class]);

return $router;
