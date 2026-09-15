<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Excel;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\AuditRepository;
use App\Repositories\OrangRepository;
use App\Services\OrangService;

final class OrangController
{
    private OrangService $svc;
    private OrangRepository $repo;

    public function __construct()
    {
        $pdo = Database::connection();
        $this->repo = new OrangRepository($pdo);
        $this->svc = new OrangService($pdo, $this->repo, new AuditRepository($pdo));
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
        $perPage = 15;
        $res = $this->repo->paginate($q, $page, $perPage, $status, $role);
        Response::view('orang/index.phtml', [
            'rows' => $res['data'],
            'total' => $res['total'],
            'roles' => $this->repo->roles(),
            'rekap' => $this->repo->countByRole(),
            'q' => $q, 'role' => $role, 'status' => $status, 'page' => $page, 'perPage' => $perPage,
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
            'import_errors' => Session::flash('import_errors') ?? [],
        ]);
    }

    /** GET /petugas/export — unduh xlsx semua petugas (mengikuti filter aktif). */
    public function exportExcel(Request $req, array $params = []): void
    {
        $q = (string) ($req->get['q'] ?? '');
        $role = (string) ($req->get['role'] ?? 'all');
        $status = (string) ($req->get['status'] ?? 'all');
        $rows = [];
        foreach ($this->repo->exportAll($q, $status, $role) as $r) {
            $rows[] = [
                $r['nama'],
                (string) ($r['role_label'] ?? ($r['role_code'] ?? '—')),
                (string) ($r['no_hp'] ?? ''),
                (string) ($r['email'] ?? ''),
                (string) ($r['alamat'] ?? ''),
                ((int) $r['is_aktif'] === 1 ? 'Aktif' : 'Nonaktif'),
                (string) $r['jml_alias'],
            ];
        }
        Excel::download(
            'petugas_' . date('Ymd_His') . '.xlsx',
            ['Nama', 'Level / Peran', 'No HP', 'Email', 'Alamat', 'Status', 'Jml Alias'],
            $rows,
            'Petugas'
        );
    }

    /** GET /petugas/template — unduh template impor. */
    public function templateExcel(Request $req, array $params = []): void
    {
        Excel::download(
            'template_petugas.xlsx',
            ['Nama', 'Level', 'No HP', 'Email', 'Alamat', 'Status'],
            [
                ['Junaidi Ari Siswanto', 'Pencacah Lapangan', '081234567890', 'junaidi@mail.id', 'Jl. Bhayangkara 12, Sumbersari', 'Aktif'],
                ['Aminatus Sholeha', 'Pengolah Data', '081298765432', '', 'Jl. Kalimantan 5, Kaliwates', 'Aktif'],
                ['Budi Santoso', 'Pengawas Lapangan', '081211112222', 'budi@mail.id', 'Jl. Gajah Mada 10, Kaliwates', 'Aktif'],
            ],
            'Petugas',
            [
                ['PETUNJUK IMPOR PETUGAS'],
                ['1. Jangan ubah baris header / urutan kolom.'],
                ['2. Kolom Nama wajib diisi (min. 3 huruf); nama yang sudah terdaftar akan DILEWATI.'],
                ['3. Kolom Level: isi salah satu dari: Pencacah Lapangan, Pengawas Lapangan, Pengolah Data, Pengawas Pengolahan, Operator, Admin, SM Sosial, SM PLS, Viewer (atau kode: PCL, PML, PENGOLAH, PENGAWAS_OLAH, OPERATOR, ADMIN, SM_SOSIAL, SM_PLS, VIEWER).'],
                ['4. Kolom Status: isi "Aktif" atau "Nonaktif" (kosong = Aktif).'],
                ['5. Format file .xlsx, maksimal 2000 baris per impor.'],
            ]
        );
    }

    /** POST /petugas/import — impor petugas dari file Excel. */
    public function importExcel(Request $req, array $params = []): void
    {
        $cek = Excel::cekUpload();
        if ($cek !== null) {
            Session::flash('error', $cek);
            Response::redirect('/petugas');
        }
        try {
            $data = Excel::readFirstSheet($_FILES['file_excel']['tmp_name']);
        } catch (\Throwable $e) {
            Session::flash('error', 'File Excel tidak valid / rusak: ' . $e->getMessage());
            Response::redirect('/petugas');
        }
        if ($data['rows'] === []) {
            Session::flash('error', 'File kosong — tidak ada baris data.');
            Response::redirect('/petugas');
        }
        if (count($data['rows']) > Excel::maxImportRows()) {
            Session::flash('error', 'Maksimal ' . Excel::maxImportRows() . ' baris per impor.');
            Response::redirect('/petugas');
        }

        $rolesList = $this->repo->roles();
        $roleMap = [];
        foreach ($rolesList as $rl) {
            $roleMap[strtoupper(trim($rl['code']))] = (int) $rl['id'];
            $roleMap[strtoupper(trim($rl['label']))] = (int) $rl['id'];
        }
        // Alias nama level
        if (isset($roleMap['PCL'])) {
            $roleMap['PENCACAH'] = $roleMap['PCL'];
            $roleMap['PENCACAH LAPANGAN'] = $roleMap['PCL'];
        }
        if (isset($roleMap['PML'])) {
            $roleMap['PENGAWAS'] = $roleMap['PML'];
            $roleMap['PENGAWAS LAPANGAN'] = $roleMap['PML'];
        }
        if (isset($roleMap['PENGOLAH'])) {
            $roleMap['PENGOLAH DATA'] = $roleMap['PENGOLAH'];
            $roleMap['OPERATOR ENTRY'] = $roleMap['PENGOLAH'];
        }
        if (isset($roleMap['PENGAWAS_OLAH'])) {
            $roleMap['PENGAWAS PENGOLAHAN'] = $roleMap['PENGAWAS_OLAH'];
            $roleMap['PENGAWAS OLAH'] = $roleMap['PENGAWAS_OLAH'];
        }
        if (isset($roleMap['SM_SOSIAL'])) {
            $roleMap['SM SOSIAL'] = $roleMap['SM_SOSIAL'];
            $roleMap['SM SOSEK'] = $roleMap['SM_SOSIAL'];
            $roleMap['SOSIAL'] = $roleMap['SM_SOSIAL'];
        }
        if (isset($roleMap['SM_PLS'])) {
            $roleMap['SM PLS'] = $roleMap['SM_PLS'];
            $roleMap['SM OLAH'] = $roleMap['SM_PLS'];
            $roleMap['IPDS'] = $roleMap['SM_PLS'];
            $roleMap['PLS'] = $roleMap['SM_PLS'];
        }

        $ok = 0;
        $errors = [];
        foreach ($data['rows'] as $i => $row) {
            $no = $i + 2; // +header
            $rawRole = strtoupper(trim((string) Excel::pick($row, ['Level', 'Level / Peran', 'Peran', 'Jabatan'])));
            $roleId = $rawRole !== '' ? ($roleMap[$rawRole] ?? null) : null;

            $in = [
                'nama' => Excel::pick($row, ['Nama', 'Nama Lengkap', 'Nama Petugas']),
                'role_id' => $roleId,
                'no_hp' => Excel::pick($row, ['No HP', 'No. HP', 'HP', 'Telepon', 'WA', 'No HP/WA']),
                'email' => Excel::pick($row, ['Email', 'E-mail']),
                'alamat' => Excel::pick($row, ['Alamat', 'Alamat Rumah']),
                'is_aktif' => Excel::statusToAktif(Excel::pick($row, ['Status'])),
            ];
            if ($in['nama'] === '') {
                $errors[] = "Baris $no: Nama kosong — dilewati.";
                continue;
            }
            $res = $this->svc->create($in, $this->actor(), $req->ip(), $req->userAgent());
            if ($res['ok']) {
                $ok++;
            } else {
                $errors[] = "Baris $no (" . $in['nama'] . '): ' . implode(' ', $res['errors']);
            }
        }
        Session::flash('success', "Import selesai: $ok petugas berhasil ditambah, " . count($errors) . ' baris dilewati/gagal.');
        if ($errors !== []) {
            Session::flash('import_errors', $errors);
        }
        Response::redirect('/petugas');
    }

    public function create(Request $req, array $params = []): void
    {
        Response::view('orang/form.phtml', [
            'mode' => 'create', 'row' => Session::flash('old') ?? [],
            'roles' => $this->repo->roles(),
            'errors' => Session::flash('errors') ?? [],
            'csrf' => \App\Core\Csrf::field(),
        ]);
    }

    public function store(Request $req, array $params = []): void
    {
        $in = [
            'nama' => $req->post['nama'] ?? '',
            'role_id' => !empty($req->post['role_id']) ? (int) $req->post['role_id'] : null,
            'no_hp' => $req->post['no_hp'] ?? '',
            'email' => $req->post['email'] ?? '',
            'alamat' => $req->post['alamat'] ?? '',
            'is_aktif' => isset($req->post['is_aktif']) ? 1 : 0,
        ];
        $res = $this->svc->create($in, $this->actor(), $req->ip(), $req->userAgent());
        if (!$res['ok']) {
            Session::flash('errors', $res['errors']);
            Session::flash('old', $in);
            Response::redirect('/petugas/baru');
        }
        Session::flash('success', 'Petugas berhasil ditambah.');
        Response::redirect('/petugas');
    }

    public function show(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $row = $this->repo->find($id);
        if ($row === null) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.phtml';
            exit;
        }
        Response::view('orang/show.phtml', [
            'row' => $row,
            'aliases' => $this->repo->aliases($id),
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
            'csrf' => \App\Core\Csrf::field(),
        ]);
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
        Response::view('orang/form.phtml', [
            'mode' => 'edit', 'id' => $id,
            'row' => Session::flash('old') ?? $row,
            'roles' => $this->repo->roles(),
            'errors' => Session::flash('errors') ?? [],
            'csrf' => \App\Core\Csrf::field(),
        ]);
    }

    public function update(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $in = [
            'nama' => $req->post['nama'] ?? '',
            'role_id' => !empty($req->post['role_id']) ? (int) $req->post['role_id'] : null,
            'no_hp' => $req->post['no_hp'] ?? '',
            'email' => $req->post['email'] ?? '',
            'alamat' => $req->post['alamat'] ?? '',
            'is_aktif' => isset($req->post['is_aktif']) ? 1 : 0,
        ];
        $res = $this->svc->update($id, $in, $this->actor(), $req->ip(), $req->userAgent());
        if (!$res['ok']) {
            Session::flash('errors', $res['errors']);
            Session::flash('old', $in);
            Response::redirect('/petugas/' . $id . '/edit');
        }
        Session::flash('success', 'Petugas berhasil diperbarui.');
        Response::redirect('/petugas/' . $id);
    }

    public function toggle(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $row = $this->repo->find($id);
        if ($row === null) {
            Session::flash('error', 'Data tidak ditemukan.');
            Response::redirect('/petugas');
        }
        $this->svc->setAktif($id, (int) $row['is_aktif'] !== 1, $this->actor(), $req->ip(), $req->userAgent());
        Session::flash('success', 'Status petugas diperbarui.');
        Response::redirect('/petugas/' . $id);
    }

    public function addAlias(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $res = $this->svc->addAlias($id, (string) ($req->post['alias'] ?? ''), $this->actor(), $req->ip(), $req->userAgent());
        if (!$res['ok']) {
            Session::flash('error', $res['error']);
        } else {
            Session::flash('success', 'Alias berhasil ditambah.');
        }
        Response::redirect('/petugas/' . $id);
    }

    public function deleteAlias(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $aliasId = (int) ($req->post['alias_id'] ?? 0);
        $this->repo->deleteAlias($aliasId);
        Session::flash('success', 'Alias dihapus.');
        Response::redirect('/petugas/' . $id);
    }
}
