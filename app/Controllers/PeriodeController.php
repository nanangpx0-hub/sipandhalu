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
use App\Repositories\SampelRepository;
use App\Repositories\SampelRutaRepository;
use App\Services\DokumenKirimService;
use App\Repositories\DokumenRepository;
use App\Repositories\SlsRepository;
use App\Services\DokumenService;
use App\Services\PenugasanService;

final class PeriodeController
{
    private PeriodeRepository $per;
    private SampelRepository $sampel;
    private SlsRepository $sls;
    private DokumenRepository $dokumenRepo;
    private DokumenService $dokumenSvc;
    private SampelRutaRepository $rutaRepo;
    private DokumenKirimService $dokKirimSvc;

    public function __construct()
    {
        $pdo = Database::connection();
        $this->per = new PeriodeRepository($pdo);
        $this->sampel = new SampelRepository($pdo);
        $this->sls = new SlsRepository($pdo);
        $this->dokumenRepo = new DokumenRepository($pdo);
        $this->dokumenSvc = new DokumenService($pdo, $this->sampel, $this->dokumenRepo, new AuditRepository($pdo));
        $this->rutaRepo = new SampelRutaRepository($pdo);
        $this->dokKirimSvc = new DokumenKirimService($pdo, $this->rutaRepo, new AuditRepository($pdo));
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
            'import_errors' => Session::flash('import_errors') ?? [],
        ]);
    }

    /** GET /periode/export — unduh xlsx daftar periode. */
    public function exportExcel(Request $req, array $params = []): void
    {
        $rows = [];
        foreach ($this->per->all() as $r) {
            $rows[] = [
                (string) $r['tahun'],
                (string) $r['jenis'],
                (string) $r['label'],
                (string) ($r['tgl_mulai'] ?? ''),
                (string) ($r['tgl_selesai'] ?? ''),
                (string) $r['status'],
                (string) ($r['catatan'] ?? ''),
            ];
        }
        Excel::download(
            'periode_' . date('Ymd_His') . '.xlsx',
            ['Tahun', 'Jenis', 'Label', 'Mulai', 'Selesai', 'Status', 'Catatan'],
            $rows,
            'Periode'
        );
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
        // Opsi petugas aktif untuk dropdown penugasan (hindari input ID manual).
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT id, nama FROM orang WHERE is_aktif=1 ORDER BY nama LIMIT 2000');
        $stmt->execute();
        $petugasOptions = $stmt->fetchAll();
        // Peta peran per orang di periode ini untuk indikator K4 instan.
        $stmt = $pdo->prepare(
            'SELECT pg.pcl_id AS oid, "PCL" AS peran FROM penugasan pg JOIN sampel sp ON sp.id=pg.sampel_id WHERE sp.periode_id=:p1 AND pg.pcl_id IS NOT NULL
             UNION ALL
             SELECT pg.pml_id, "PML" FROM penugasan pg JOIN sampel sp ON sp.id=pg.sampel_id WHERE sp.periode_id=:p2 AND pg.pml_id IS NOT NULL
             UNION ALL
             SELECT pg.pengolah_id, "PENGOLAH" FROM penugasan pg JOIN sampel sp ON sp.id=pg.sampel_id WHERE sp.periode_id=:p3 AND pg.pengolah_id IS NOT NULL'
        );
        $stmt->execute([':p1' => $id, ':p2' => $id, ':p3' => $id]);
        $peranMap = [];
        foreach ($stmt->fetchAll() as $r) {
            $oid = (int) $r['oid'];
            $peranMap[$oid][] = $r['peran'];
            $peranMap[$oid] = array_values(array_unique($peranMap[$oid]));
        }
        Response::view('periode/show.phtml', [
            'row' => $row, 'ringkas' => $this->per->ringkasan($id),
            'beban' => $this->per->bebanPengolah($id),
            'sampel' => $res['data'], 'total' => $res['total'],
            'q' => $q, 'page' => $page, 'perPage' => 15,
            'petugasOptions' => $petugasOptions, 'peranMap' => $peranMap,
            'pmlList' => $this->sampel->allPmlInPeriode($id),
            'rutaSummary' => $this->rutaRepo->getSummaryByPeriodeId($id),
            'success' => Session::flash('success'), 'error' => Session::flash('error'),
            'import_errors' => Session::flash('import_errors') ?? [],
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

    /** GET /periode/{id}/template-sampel — template impor daftar sampel 1 periode. */
    public function templateSampel(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $row = $this->per->find($id);
        if ($row === null) {
            Session::flash('error', 'Periode tidak ditemukan.');
            Response::redirect('/periode');
        }
        Excel::download(
            'template_sampel_periode_' . $id . '.xlsx',
            ['NKS', 'Target'],
            [
                ['56361', '10'],
                ['56362', '10'],
            ],
            'Sampel ' . $row['label'],
            [
                ['PETUNJUK IMPOR SAMPEL PERIODE: ' . $row['label']],
                ['1. Jangan ubah baris header / urutan kolom.'],
                ['2. Kolom NKS wajib: 5 digit angka dan harus sudah ada di master SLS (menu SLS).'],
                ['3. Kolom Target: jumlah target sampel per SLS (kosong = 10).'],
                ['4. NKS yang sudah ada di periode ini akan DILEWATI.'],
                ['5. Periode berstatus TUTUP tidak bisa menerima sampel baru.'],
            ]
        );
    }

    /** POST /periode/{id}/import-sampel — impor daftar sampel dari file Excel. */
    public function importSampel(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $row = $this->per->find($id);
        if ($row === null) {
            Session::flash('error', 'Periode tidak ditemukan.');
            Response::redirect('/periode');
        }
        if ($row['status'] === 'TUTUP') {
            Session::flash('error', 'Periode sudah TUTUP, tidak bisa menambah sampel.');
            Response::redirect('/periode/' . $id);
        }
        $cek = Excel::cekUpload();
        if ($cek !== null) {
            Session::flash('error', $cek);
            Response::redirect('/periode/' . $id);
        }
        try {
            $data = Excel::readFirstSheet($_FILES['file_excel']['tmp_name']);
        } catch (\Throwable $e) {
            Session::flash('error', 'File Excel tidak valid / rusak: ' . $e->getMessage());
            Response::redirect('/periode/' . $id);
        }
        if ($data['rows'] === []) {
            Session::flash('error', 'File kosong — tidak ada baris data.');
            Response::redirect('/periode/' . $id);
        }
        if (count($data['rows']) > Excel::maxImportRows()) {
            Session::flash('error', 'Maksimal ' . Excel::maxImportRows() . ' baris per impor.');
            Response::redirect('/periode/' . $id);
        }
        $ok = 0;
        $errors = [];
        foreach ($data['rows'] as $i => $r) {
            $no = $i + 2; // +header
            $nks = str_pad(Excel::pick($r, ['NKS', 'Kode NKS']), 5, '0', STR_PAD_LEFT);
            if (!preg_match('/^[0-9]{5}$/', $nks)) {
                $errors[] = "Baris $no: NKS tidak valid (harus 5 digit).";
                continue;
            }
            $sls = $this->sls->findByNks($nks);
            if ($sls === null) {
                $errors[] = "Baris $no: NKS $nks tidak ada di master SLS.";
                continue;
            }
            $target = Excel::pick($r, ['Target', 'Target Sampel']);
            $target = ($target === '' || !ctype_digit($target)) ? 10 : (int) $target;
            try {
                $this->sampel->add($id, (int) $sls['id'], $target, null);
                $ok++;
            } catch (\Throwable $e) {
                $errors[] = "Baris $no: " . (str_contains($e->getMessage(), 'Duplicate') ? "NKS $nks sudah ada di periode ini." : $e->getMessage());
            }
        }
        Session::flash('success', "Import sampel selesai: $ok SLS masuk periode {$row['label']}, " . count($errors) . ' baris dilewati/gagal.');
        if ($errors !== []) {
            Session::flash('import_errors', $errors);
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

    public function terimaDokumen(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $sid = (int) ($params['sid'] ?? 0);
        $operatorId = $this->actor() ?? 0;
        try {
            $this->dokumenSvc->terimaSatuan(
                $sid,
                $req->post,
                $operatorId,
                $req->ip(),
                $req->userAgent()
            );
            Session::flash('success', 'Penerimaan dokumen berhasil dicatat.');
        } catch (\Throwable $t) {
            Session::flash('error', $t->getMessage());
        }
        Response::redirect('/periode/' . $id);
    }

    public function ajaxPmlSampel(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $pmlId = (int) ($params['pmlId'] ?? 0);
        $rows = $this->sampel->findByPmlInPeriode($id, $pmlId);
        Response::json(['data' => $rows]);
    }

    public function terimaKolektif(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $pmlId = (int) ($req->post['pml_id'] ?? 0);
        $waktuTerima = trim((string) ($req->post['waktu_terima'] ?? '')) ?: date('Y-m-d H:i:s');
        $catatan = trim((string) ($req->post['catatan'] ?? ''));
        $rawItems = $req->post['items'] ?? [];
        $items = [];
        if (is_array($rawItems)) {
            foreach ($rawItems as $sid => $val) {
                if (!empty($val['pilih'])) {
                    $items[] = [
                        'sampel_id' => (int) $sid,
                        'pemutakhiran' => !empty($val['pemutakhiran']),
                        'peta' => !empty($val['peta']),
                        'hasil_kk' => isset($val['hasil_kk']) && $val['hasil_kk'] !== '' ? (int) $val['hasil_kk'] : null,
                        'hasil_rt' => isset($val['hasil_rt']) && $val['hasil_rt'] !== '' ? (int) $val['hasil_rt'] : null,
                    ];
                }
            }
        }
        $operatorId = $this->actor() ?? 0;
        try {
            if (empty($items)) {
                throw new \InvalidArgumentException('Tidak ada sampel yang dipilih untuk diterima.');
            }
            $count = $this->dokumenSvc->terimaKolektif(
                $id,
                $pmlId,
                $items,
                $waktuTerima,
                $catatan,
                $operatorId,
                $req->ip(),
                $req->userAgent()
            );
            Session::flash('success', "Penerimaan kolektif berhasil ({$count} sampel diperbarui).");
        } catch (\Throwable $t) {
            Session::flash('error', $t->getMessage());
        }
        Response::redirect('/periode/' . $id);
    }

    public function pinjamDokumen(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $sid = (int) ($params['sid'] ?? 0);
        $jenisDok = trim((string) ($req->post['jenis_dok'] ?? 'SEMUA'));
        $peminjamId = (int) ($req->post['peminjam_id'] ?? 0);
        $peminjamPeran = trim((string) ($req->post['peminjam_peran'] ?? 'PML'));
        $waktuPinjam = trim((string) ($req->post['waktu_pinjam'] ?? '')) ?: date('Y-m-d H:i:s');
        $alasan = trim((string) ($req->post['alasan'] ?? ''));
        $operatorId = $this->actor() ?? 0;

        try {
            $this->dokumenSvc->pinjamDokumen(
                $sid,
                $jenisDok,
                $peminjamId,
                $peminjamPeran,
                $waktuPinjam,
                $alasan,
                $operatorId,
                $req->ip(),
                $req->userAgent()
            );
            Session::flash('success', 'Peminjaman dokumen berhasil dicatat.');
        } catch (\Throwable $t) {
            Session::flash('error', $t->getMessage());
        }
        Response::redirect('/periode/' . $id);
    }

    public function kembaliDokumen(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $pinjamId = (int) ($req->post['pinjam_id'] ?? 0);
        $waktuKembali = trim((string) ($req->post['waktu_kembali'] ?? '')) ?: date('Y-m-d H:i:s');
        $catatan = trim((string) ($req->post['catatan_kembali'] ?? ''));
        $operatorId = $this->actor() ?? 0;

        try {
            $this->dokumenSvc->kembalikanDokumen(
                $pinjamId,
                $waktuKembali,
                $catatan,
                $operatorId,
                $req->ip(),
                $req->userAgent()
            );
            Session::flash('success', 'Pengembalian dokumen berhasil dicatat (status kembali DITERIMA).');
        } catch (\Throwable $t) {
            Session::flash('error', $t->getMessage());
        }
        Response::redirect('/periode/' . $id);
    }

    public function riwayatDokumen(Request $req, array $params = []): void
    {
        $sid = (int) ($params['sid'] ?? 0);
        $sampel = $this->sampel->find($sid);
        $riwayat = $this->dokumenRepo->riwayatBySampel($sid);
        $activePinjam = $this->dokumenRepo->activePinjamBySampel($sid);
        Response::json([
            'sampel' => $sampel,
            'riwayat' => $riwayat,
            'activePinjam' => $activePinjam,
        ]);
    }

    /* ==================== DOKUMEN KIRIM KAB (RUTA) ==================== */

    /** GET /periode/{id}/sampel/{sid}/ruta — data 10 ruta untuk modal rincian. */
    public function ajaxRuta(Request $req, array $params = []): void
    {
        $sid = (int) ($params['sid'] ?? 0);
        $this->dokKirimSvc->initRutaForSampel($sid);
        Response::json([
            'sampel' => $this->sampel->find($sid),
            'ruta' => $this->rutaRepo->getBySampelId($sid),
        ]);
    }

    /** POST /periode/{id}/sampel/{sid}/ruta — simpan satu baris ruta (AJAX). */
    public function simpanRuta(Request $req, array $params = []): void
    {
        $sid = (int) ($params['sid'] ?? 0);
        $data = [
            'no_urut_ruta' => (int) ($req->post['no_urut_ruta'] ?? 0),
            'status_selesai' => trim((string) ($req->post['status_selesai'] ?? 'BELUM')),
            'catatan_modul' => (int) !empty($req->post['catatan_modul']),
            'catatan_kp' => (int) !empty($req->post['catatan_kp']),
            'tgl_pengiriman' => trim((string) ($req->post['tgl_pengiriman'] ?? '')),
            'ttd_sos' => trim((string) ($req->post['ttd_sos'] ?? '')),
            'ttd_ipds' => trim((string) ($req->post['ttd_ipds'] ?? '')),
        ];
        try {
            $this->dokKirimSvc->updateRutaSatuan($sid, $data, $this->actor(), $req->ip(), $req->userAgent());
            Response::json(['ok' => true, 'message' => 'Baris ruta tersimpan.']);
        } catch (\Throwable $t) {
            http_response_code(400);
            Response::json(['ok' => false, 'message' => $t->getMessage()]);
        }
    }

    /** POST /periode/{id}/sampel/{sid}/ruta/selesai-semua — tandai 10 ruta selesai sekaligus. */
    public function selesaiSemuaRuta(Request $req, array $params = []): void
    {
        $sid = (int) ($params['sid'] ?? 0);
        $tgl = trim((string) ($req->post['tgl_pengiriman'] ?? '')) ?: date('Y-m-d');
        $ttd = trim((string) ($req->post['ttd'] ?? ''));
        $isAjax = (string) ($req->post['_ajax'] ?? '') === '1';
        try {
            $this->dokKirimSvc->setSemuaRutaSelesai($sid, $tgl, $ttd !== '' ? $ttd : null, $this->actor(), $req->ip(), $req->userAgent());
            if ($isAjax) {
                Response::json(['ok' => true, 'message' => '10 ruta ditandai selesai pada ' . $tgl . '.']);
            }
            Session::flash('success', 'Seluruh 10 ruta ditandai selesai pada ' . $tgl . '.');
        } catch (\Throwable $t) {
            if ($isAjax) {
                http_response_code(400);
                Response::json(['ok' => false, 'message' => $t->getMessage()]);
            }
            Session::flash('error', $t->getMessage());
        }
        Response::redirect('/periode/' . (int) ($params['id'] ?? 0));
    }

    /** POST /periode/{id}/dok-kirim/import — unggah & impor template Dok Kirim Kab. */
    public function importDokKirim(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $err = \App\Core\Excel::cekUpload();
        if ($err !== null) {
            Session::flash('error', $err);
            Response::redirect('/periode/' . $id);
        }
        $file = $_FILES['file_excel'];
        try {
            $rekap = $this->dokKirimSvc->importExcel($id, (string) $file['tmp_name'], $this->actor(), $req->ip(), $req->userAgent());
            $msg = sprintf(
                'Import Dok Kirim Kab: %d baris dibaca, %d tersimpan, %d dilewati.',
                $rekap['total'],
                $rekap['terupdate'],
                $rekap['dilewati']
            );
            if ($rekap['errors'] !== []) {
                Session::flash('import_errors', array_slice($rekap['errors'], 0, 20));
                Session::flash('success', $msg . ' Periksa detail baris bermasalah di bawah.');
            } else {
                Session::flash('success', $msg);
            }
        } catch (\Throwable $t) {
            Session::flash('error', 'Import gagal: ' . $t->getMessage());
        }
        Response::redirect('/periode/' . $id);
    }

    /** GET /periode/{id}/dok-kirim/export — unduh rekap sesuai layout template. */
    public function exportDokKirim(Request $req, array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $row = $this->per->find($id);
        $path = $this->dokKirimSvc->exportExcel($id);
        $nama = 'dokkirimkab_' . preg_replace('/[^A-Za-z0-9]+/', '_', (string) ($row['label'] ?? $id)) . '_' . date('Ymd') . '.xlsx';
        if (ob_get_level() > 0) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $nama . '"');
        header('Cache-Control: max-age=0');
        readfile($path);
        @unlink($path);
        exit;
    }
}
