<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Excel;
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
            'import_errors' => Session::flash('import_errors') ?? [],
        ]);
    }

    /** GET /sls/export — unduh xlsx master SLS (mengikuti filter aktif). */
    public function exportExcel(Request $req, array $params = []): void
    {
        $q = (string) ($req->get['q'] ?? '');
        $kec = (string) ($req->get['kec'] ?? 'all');
        $rows = [];
        foreach ($this->sls->exportAll($q, $kec) as $r) {
            $rows[] = [
                (string) $r['nks'],
                (string) ($r['kode_full'] ?? ''),
                (string) $r['kec'],
                (string) $r['desa'],
                (string) ($r['sls'] ?? ''),
                (string) ($r['sub'] ?? ''),
                (string) ($r['nama_sls'] ?? ''),
                (string) ($r['dusun'] ?? ''),
                (string) ($r['rw'] ?? ''),
                (string) ($r['rt'] ?? ''),
                (string) ($r['ketua'] ?? ''),
                (string) ($r['klasifikasi'] ?? ''),
                (string) ($r['jml_kk'] ?? ''),
                (string) ($r['jml_rt'] ?? ''),
                ((int) $r['is_aktif'] === 1 ? 'Aktif' : 'Nonaktif'),
            ];
        }
        Excel::download(
            'sls_' . date('Ymd_His') . '.xlsx',
            ['NKS', 'Kode 16', 'Kec', 'Desa', 'SLS', 'Sub', 'Nama SLS', 'Dusun', 'RW', 'RT', 'Ketua', 'Klasifikasi', 'Jml KK', 'Jml RT', 'Status'],
            $rows,
            'Master SLS'
        );
    }

    /** GET /sls/template — unduh template impor SLS. */
    public function templateExcel(Request $req, array $params = []): void
    {
        Excel::download(
            'template_sls.xlsx',
            ['NKS', 'Kode 16', 'Kec', 'Desa', 'SLS', 'Sub', 'Nama SLS', 'Dusun', 'RW', 'RT', 'Ketua', 'Klasifikasi', 'Jml KK', 'Jml RT', 'Status'],
            [
                ['56361', '3509010001000100', '001', '001', '0001', '00', 'SLS 001 RT 001', 'Dusun Kra', '001', '001', 'Bpk. Sumarno', '1', '85', '32', 'Aktif'],
                ['56362', '', '001', '001', '0002', '00', 'SLS 002 RT 002', 'Dusun Kra', '002', '003', '', '', '70', '25', 'Aktif'],
            ],
            'Master SLS',
            [
                ['PETUNJUK IMPOR MASTER SLS'],
                ['1. Jangan ubah baris header / urutan kolom.'],
                ['2. NKS wajib 5 digit angka & unik; NKS yang sudah ada akan DILEWATI.'],
                ['3. Isi Kode 16 (16 digit) ATAU Kec+Desa(3 digit)+SLS(4 digit)+Sub(2 digit).'],
                ['4. Kec/Desa harus sudah ada di master wilayah; Klasifikasi/Jml KK/Jml RT boleh kosong.'],
                ['5. Kolom Status: "Aktif"/"Nonaktif" (kosong = Aktif). Kolom kode diisi angka berformat teks.'],
                ['6. Format file .xlsx, maksimal 2000 baris per impor.'],
            ]
        );
    }

    /** POST /sls/import — impor master SLS dari file Excel (1 transaksi per baris). */
    public function importExcel(Request $req, array $params = []): void
    {
        $cek = Excel::cekUpload();
        if ($cek !== null) {
            Session::flash('error', $cek);
            Response::redirect('/sls');
        }
        try {
            $data = Excel::readFirstSheet($_FILES['file_excel']['tmp_name']);
        } catch (\Throwable $e) {
            Session::flash('error', 'File Excel tidak valid / rusak: ' . $e->getMessage());
            Response::redirect('/sls');
        }
        if ($data['rows'] === []) {
            Session::flash('error', 'File kosong — tidak ada baris data.');
            Response::redirect('/sls');
        }
        if (count($data['rows']) > Excel::maxImportRows()) {
            Session::flash('error', 'Maksimal ' . Excel::maxImportRows() . ' baris per impor.');
            Response::redirect('/sls');
        }
        $pdo = Database::connection();
        $audit = new AuditRepository($pdo);
        $ok = 0;
        $errors = [];
        foreach ($data['rows'] as $i => $row) {
            $no = $i + 2; // +header
            $aktif = Excel::statusToAktif(Excel::pick($row, ['Status']));
            $in = [
                'kode_full' => Excel::pick($row, ['Kode 16', 'Kode SLS', 'Kode Full', 'Kode SLS 16']),
                'kec' => Excel::pick($row, ['Kec', 'Kode Kec', 'Kode Kecamatan', 'Kecamatan']),
                'desa' => Excel::pick($row, ['Desa', 'Kode Desa']),
                'sls' => Excel::pick($row, ['SLS', 'Kode SLS4', 'Sls4']),
                'sub' => Excel::pick($row, ['Sub', 'Sub SLS', 'Sub-SLS']),
                'nks' => Excel::pick($row, ['NKS']),
                'dusun' => Excel::pick($row, ['Dusun', 'Nama Dusun']),
                'rw' => Excel::pick($row, ['RW']),
                'rt' => Excel::pick($row, ['RT']),
                'nama_sls' => Excel::pick($row, ['Nama SLS']),
                'ketua' => Excel::pick($row, ['Ketua', 'Nama Ketua']),
                'klasifikasi' => Excel::pick($row, ['Klasifikasi']),
                'jml_kk' => Excel::pick($row, ['Jml KK', 'Jumlah KK']),
                'jml_rt' => Excel::pick($row, ['Jml RT', 'Jumlah RT']),
                // normalize() membaca is_aktif hanya via isset(); nilai asli di-set ulang di bawah
                'is_aktif' => 1,
            ];
            $n = $this->svc->normalize($in);
            if (!$n['ok']) {
                $errors[] = "Baris $no: " . implode(' ', $n['errors']);
                continue;
            }
            $n['data']['is_aktif'] = $aktif;
            $pdo->beginTransaction();
            try {
                $id = $this->sls->create($n['data']);
                $audit->log($this->actor(), 'CREATE', 'sls', (string) $id, null, $this->sls->find($id), $req->ip(), $req->userAgent());
                $pdo->commit();
                $ok++;
            } catch (\Throwable $e) {
                $pdo->rollBack();
                $errors[] = "Baris $no: " . (str_contains($e->getMessage(), 'Duplicate') ? 'NKS/kode sudah terdaftar.' : $e->getMessage());
            }
        }
        Session::flash('success', "Import selesai: $ok SLS ditambah, " . count($errors) . ' baris dilewati/gagal.');
        if ($errors !== []) {
            Session::flash('import_errors', $errors);
        }
        Response::redirect('/sls');
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
