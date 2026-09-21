<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditRepository;
use App\Repositories\SampelRutaRepository;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDO;
use RuntimeException;

final class PengolahanService
{
    /**
     * Peran yang berhak mengubah data/status pengolahan (read-write).
     * ADMIN + OPERATOR + SM_PLS (Subject Matter Tim IPDS / Pengolahan & Layanan Statistik).
     *
     * @var array<int,string>
     */
    public const EDIT_ROLES = ['ADMIN', 'OPERATOR', 'SM_PLS'];

    /** Pesan penolakan standar (dipakai service & controller agar konsisten). */
    public const EDIT_DENIED_MESSAGE = 'Akses ditolak: Anda tidak memiliki wewenang untuk mengubah data atau status pengolahan dokumen. Wewenang ini khusus Admin dan Operator Tim IPDS.';

    /** Kode status HTTP untuk penolakan wewenang (dipetakan controller → 403 Forbidden). */
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_BAD_REQUEST = 400;

    public function __construct(
        private PDO $pdo,
        private SampelRutaRepository $rutaRepo,
        private AuditRepository $auditRepo
    ) {
    }

    /**
     * Hak akses tulis status fisik dokumen (Penerimaan dokumen fisik).
     * ADMIN + OPERATOR + SM_PLS (Subject Matter Tim IPDS).
     *
     * @param array<string,mixed> $user
     */
    public function canEditDocument(array $user): bool
    {
        $role = (string) ($user['role_code'] ?? $user['role'] ?? 'VIEWER');

        return in_array($role, ['ADMIN', 'OPERATOR', 'SM_PLS'], true);
    }

    /**
     * Hak akses input temuan error pengolah (ket_kp_pengolah & ket_m_pengolah).
     * PENGOLAH (binaan sendiri), ADMIN, OPERATOR, SM_PLS.
     *
     * @param array<string,mixed> $user
     */
    public function canEditPengolahCatatan(array $user): bool
    {
        $role = (string) ($user['role_code'] ?? $user['role'] ?? 'VIEWER');

        return in_array($role, ['ADMIN', 'OPERATOR', 'SM_PLS', 'PENGOLAH'], true);
    }

    /**
     * Hak akses input catatan uji petik supervisi pengawas pengolahan.
     * HANYA PENGAWAS_OLAH dan ADMIN.
     *
     * @param array<string,mixed> $user
     */
    public function canEditPengawasCatatan(array $user): bool
    {
        $role = (string) ($user['role_code'] ?? $user['role'] ?? 'VIEWER');

        return in_array($role, ['ADMIN', 'PENGAWAS_OLAH'], true);
    }

    /**
     * Hak akses melakukan transfer data pengolahan (Transfer K, KP, Seruti).
     * Sesuai ketentuan: Petugas Pengolah Data, Pengawas Pengolahan, Operator, dan Admin.
     * (PENGOLAH dibatasi pada ruta binaan sendiri di tingkat eksekusi).
     *
     * @param array<string,mixed> $user
     */
    public function canTransfer(array $user): bool
    {
        $role = (string) ($user['role_code'] ?? $user['role'] ?? 'VIEWER');

        return in_array($role, ['ADMIN', 'OPERATOR', 'SM_PLS', 'PENGOLAH', 'PENGAWAS_OLAH'], true);
    }

    /**
     * Khusus transfer Seruti: hanya Operator (Tim IPDS), Pengawas Pengolahan, dan Admin.
     * Peran PENGOLAH (Petugas Pengolah Data) TIDAK BISA melakukan transfer Seruti.
     *
     * @param array<string,mixed> $user
     */
    public function canTransferSeruti(array $user): bool
    {
        $role = (string) ($user['role_code'] ?? $user['role'] ?? 'VIEWER');

        return in_array($role, ['ADMIN', 'OPERATOR', 'SM_PLS', 'PENGAWAS_OLAH'], true);
    }

    /**
     * Hak akses tulis modul LK Pengolahan Sampel (secara umum).
     * TRUE untuk ADMIN, OPERATOR, SM_PLS, PENGOLAH, dan PENGAWAS_OLAH.
     * Peran lain (SM_SOSIAL, PML, PCL, VIEWER) read-only mutlak.
     *
     * @param array<string,mixed> $user
     */
    public function canEdit(array $user): bool
    {
        return $this->canEditDocument($user) 
            || $this->canEditPengolahCatatan($user) 
            || $this->canEditPengawasCatatan($user);
    }

    /** Penegakan mutlak hak tulis status fisik dokumen; melanggar → RuntimeException ber-kode 403. */
    public function assertCanEditDocument(array $user): void
    {
        if (!$this->canEditDocument($user)) {
            throw new RuntimeException(self::EDIT_DENIED_MESSAGE, self::HTTP_FORBIDDEN);
        }
    }

    /** Penegakan mutlak hak tulis; melanggar → RuntimeException ber-kode 403. */
    private function assertCanEdit(array $user): void
    {
        if (!$this->canEdit($user)) {
            throw new RuntimeException(self::EDIT_DENIED_MESSAGE, self::HTTP_FORBIDDEN);
        }
    }

    /**
     * Ambil daftar ruta pengolahan dengan filter hak akses berdasarkan role pengguna.
     */
    public function getDaftarRuta(int $periodeId, array $filters, array $currentUser): array
    {
        $role = (string) ($currentUser['role_code'] ?? $currentUser['role'] ?? 'VIEWER');
        $orangId = (int) ($currentUser['orang_id'] ?? 0);

        // PENGOLAH: paksa cakupan data binaan sendiri (abaikan parameter URL).
        if ($role === 'PENGOLAH') {
            $filters['pengolah_id'] = $orangId > 0 ? $orangId : -1;
        } elseif ($role === 'PCL' && $orangId > 0 && empty($filters['pcl_id'])) {
            // Filter default per peran jika belum dipilih secara eksplisit
            $filters['pcl_id'] = $orangId;
        } elseif ($role === 'PML' && $orangId > 0 && empty($filters['pml_id'])) {
            $filters['pml_id'] = $orangId;
        }

        return $this->rutaRepo->getPengolahanRuta($periodeId, $filters);
    }

    /**
     * Ambil ringkasan progres pengolahan untuk dashboard dan header.
     */
    public function getSummary(int $periodeId, ?int $pengolahId = null): array
    {
        return $this->rutaRepo->getPengolahanSummary($periodeId, $pengolahId);
    }

    /**
     * Jadwal piket pengawas pengolahan 1 periode (urut tanggal).
     * @return array<int,array<string,mixed>>
     */
    public function getJadwalPengawas(int $periodeId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT j.*, o.nama AS orang_nama
                 FROM jadwal_pengawas_pengolahan j
                 LEFT JOIN orang o ON o.id = j.orang_id
                 WHERE j.periode_id = :p
                 ORDER BY j.tanggal ASC'
            );
            $stmt->execute([':p' => $periodeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Tabel belum dimigrasi (DB lama) → kembalikan kosong agar UI/export tetap jalan.
            return [];
        }
    }

    /**
     * Pengawas bertugas pada tanggal tertentu (default hari ini).
     * Return NULL bila LIBUR / di luar rentang jadwal.
     */
    public function getPengawasHariIni(int $periodeId, ?string $tanggal = null): ?array
    {
        $tgl = $tanggal ?? date('Y-m-d');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl) !== 1) {
            return null;
        }
        try {
            $stmt = $this->pdo->prepare(
                'SELECT j.*, o.nama AS orang_nama
                 FROM jadwal_pengawas_pengolahan j
                 LEFT JOIN orang o ON o.id = j.orang_id
                 WHERE j.periode_id = :p AND j.tanggal = :t LIMIT 1'
            );
            $stmt->execute([':p' => $periodeId, ':t' => $tgl]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Tabel belum dimigrasi (DB lama) → anggap tidak ada jadwal.
            return null;
        }
        if ($row === false || ($row['status'] ?? '') !== 'TUGAS') {
            return null;
        }
        return $row;
    }

    /**
     * Update data pengolahan satu baris ruta (via AJAX / Form) dengan validasi RBAC & audit log.
     * - PENGOLAH: hanya ruta binaan sendiri, boleh input temuan error KP/Modul & transfer, dilarang input uji petik pengawas.
     * - PENGAWAS_OLAH: hanya boleh input catatan supervisi/uji petik pengawas pengolahan.
     * - ADMIN: hak penuh atas semua kolom.
     * - OPERATOR/SM_PLS (Tim IPDS): penerimaan fisik dokumen, telaah IPDS, error pengolah, transfer, dilarang uji petik pengawas.
     */
    public function updateRuta(int $rutaId, array $data, array $currentUser): array
    {
        $before = $this->rutaRepo->findRutaById($rutaId);
        if (!$before) {
            throw new RuntimeException('Data ruta tidak ditemukan.');
        }

        $role = (string) ($currentUser['role_code'] ?? $currentUser['role'] ?? 'VIEWER');
        $orangId = (int) ($currentUser['orang_id'] ?? 0);
        $userId = (int) ($currentUser['id'] ?? 0);

        // Validasi umum: pengguna harus memiliki salah satu hak akses tulis di modul pengolahan
        if (!$this->canEdit($currentUser)) {
            throw new RuntimeException(self::EDIT_DENIED_MESSAGE, self::HTTP_FORBIDDEN);
        }

        // Validasi Sampel Seruti Aktif: Sampel Seruti hanya aktif dan bisa diolah jika dokumen Susenas sudah selesai diolah dan ditransfer
        $isSerutiPeriode = str_starts_with((string) ($before['periode_jenis'] ?? ''), 'SERUTI');
        if ($isSerutiPeriode && empty($before['is_seruti_aktif'])) {
            throw new RuntimeException('Akses ditolak: Sampel Seruti untuk NKS ini belum aktif. Dokumen Susenas harus selesai diolah dan ditransfer ke Seruti terlebih dahulu.', self::HTTP_FORBIDDEN);
        }

        // 1. Validasi khusus role PENGOLAH
        if ($role === 'PENGOLAH') {
            // Wajib ruta binaannya sendiri
            if ($orangId <= 0 || (int) ($before['pengolah_id'] ?? 0) !== $orangId) {
                throw new RuntimeException('Akses ditolak: Anda hanya dapat mengubah data kuesioner ruta binaan Anda sendiri.', self::HTTP_FORBIDDEN);
            }

            // Catatan supervisi pengawas pengolahan TIDAK BISA diinput/diubah oleh pengolah
            if (array_key_exists('uji_petik_pengawas', $data)) {
                $oldUji = trim((string) ($before['uji_petik_pengawas'] ?? ''));
                $newUji = trim((string) $data['uji_petik_pengawas']);
                if ($newUji !== $oldUji) {
                    throw new RuntimeException('Catatan Supervisi / Uji Petik Pengawas Pengolahan hanya bisa dilakukan input oleh role pengawas pengolahan dan admin saja.', self::HTTP_FORBIDDEN);
                }
                unset($data['uji_petik_pengawas']);
            }

            // Pengolah tidak berhak mengubah penerimaan fisik dokumen tim IPDS dan telaah role lain
            unset(
                $data['status_dokumen'], $data['tgl_pengiriman'], $data['ttd_sos'], $data['ttd_ipds'],
                $data['ket_kp_ipds'], $data['ket_m_ipds'], $data['ket_kp_sosial'], $data['ket_m_sosial'],
                $data['ket_kp_lapangan'], $data['ket_m_lapangan']
            );
        }

        // 2. Validasi khusus role PENGAWAS_OLAH
        elseif ($role === 'PENGAWAS_OLAH') {
            // Pengawas pengolahan berhak mengisi catatan uji petik supervisi dan melakukan transfer (K, KP, Seruti)
            $allowedPengawas = ['uji_petik_pengawas', 'status_transfer_k', 'status_transfer_kp', 'status_transfer_seruti'];
            $data = array_intersect_key($data, array_flip($allowedPengawas));
            if (empty($data)) {
                throw new RuntimeException('Pengawas pengolahan hanya berhak mengisi catatan uji petik supervisi atau status transfer.', self::HTTP_FORBIDDEN);
            }
        }

        // 3. Validasi khusus role OPERATOR & SM_PLS (Tim IPDS non-admin)
        elseif (in_array($role, ['OPERATOR', 'SM_PLS'], true)) {
            // Catatan uji petik pengawas hanya wewenang pengawas pengolahan dan admin
            if (array_key_exists('uji_petik_pengawas', $data)) {
                $oldUji = trim((string) ($before['uji_petik_pengawas'] ?? ''));
                $newUji = trim((string) $data['uji_petik_pengawas']);
                if ($newUji !== $oldUji) {
                    throw new RuntimeException('Catatan Supervisi / Uji Petik Pengawas Pengolahan hanya bisa dilakukan input oleh role pengawas pengolahan dan admin saja.', self::HTTP_FORBIDDEN);
                }
                unset($data['uji_petik_pengawas']);
            }
        }

        // Validasi wewenang khusus Transfer Seruti: Hanya Operator, Pengawas Pengolahan, dan Admin
        if (array_key_exists('status_transfer_seruti', $data)) {
            $oldSer = (int) ($before['status_transfer_seruti'] ?? 0);
            $newSer = (int) $data['status_transfer_seruti'];
            if ($newSer !== $oldSer) {
                if (!$this->canTransferSeruti($currentUser)) {
                    throw new RuntimeException('Akses ditolak: Hanya Operator, Pengawas Pengolahan, dan Admin yang berhak melakukan transfer Seruti.', self::HTTP_FORBIDDEN);
                }
            } else {
                unset($data['status_transfer_seruti']);
            }
        }

        // Validasi prasyarat Transfer Seruti pada dokumen Susenas:
        // Dokumen Susenas harus sudah selesai diolah (Fisik ADA, Transfer K = 1, Transfer KP = 1) sebelum ditransfer ke Seruti
        if (array_key_exists('status_transfer_seruti', $data) && (int) $data['status_transfer_seruti'] === 1) {
            $isSusenasPeriode = str_starts_with((string) ($before['periode_jenis'] ?? ''), 'SUSENAS');
            if ($isSusenasPeriode) {
                $curDok = $data['status_dokumen'] ?? $before['status_dokumen'];
                $curK = isset($data['status_transfer_k']) ? (int) $data['status_transfer_k'] : (int) $before['status_transfer_k'];
                $curKp = isset($data['status_transfer_kp']) ? (int) $data['status_transfer_kp'] : (int) $before['status_transfer_kp'];
                if ($curDok !== 'ADA' || $curK !== 1 || $curKp !== 1) {
                    throw new RuntimeException(
                        'Kuesioner Susenas di NKS ini belum selesai diolah (Dokumen fisik harus ADA, serta Transfer K dan KP harus selesai) sebelum dapat ditransfer ke Seruti.',
                        self::HTTP_BAD_REQUEST
                    );
                }
            }
        }

        if (empty($data)) {
            return $before;
        }

        // Auto-populate metadata penerimaan fisik dokumen ketika status diubah menjadi ADA
        if (isset($data['status_dokumen'])) {
            if ($data['status_dokumen'] === 'ADA') {
                if (!isset($data['tgl_pengiriman']) || trim((string)$data['tgl_pengiriman']) === '') {
                    $data['tgl_pengiriman'] = !empty($before['tgl_pengiriman']) ? $before['tgl_pengiriman'] : date('Y-m-d');
                }
                if (!isset($data['ttd_sos']) || trim((string)$data['ttd_sos']) === '') {
                    $data['ttd_sos'] = !empty($before['ttd_sos']) 
                        ? $before['ttd_sos'] 
                        : (!empty($before['nama_pml']) ? trim((string)$before['nama_pml']) . ' (Tim Sosial)' : 'Tim Sosial');
                }
                if (!isset($data['ttd_ipds']) || trim((string)$data['ttd_ipds']) === '') {
                    $data['ttd_ipds'] = !empty($before['ttd_ipds']) 
                        ? $before['ttd_ipds'] 
                        : (!empty($currentUser['nama']) 
                            ? trim((string)$currentUser['nama']) . ' (Tim IPDS)' 
                            : (!empty($before['nama_pengolah']) ? trim((string)$before['nama_pengolah']) . ' (Tim IPDS)' : 'Tim IPDS'));
                }
            } elseif ($data['status_dokumen'] === 'BELUM') {
                $data['tgl_pengiriman'] = null;
                $data['ttd_sos'] = null;
                $data['ttd_ipds'] = null;
            }
        }

        $ok = $this->rutaRepo->updatePengolahanRow($rutaId, $data);
        if (!$ok) {
            throw new RuntimeException('Gagal memperbarui data ruta.');
        }

        $after = $this->rutaRepo->findRutaById($rutaId);

        // Catat audit log
        $ip = (string) ($currentUser['ip'] ?? '');
        $ua = (string) ($currentUser['user_agent'] ?? '');
        $this->auditRepo->log(
            $userId > 0 ? $userId : null,
            'UPDATE',
            'sampel_ruta',
            (string) $rutaId,
            $before,
            $after,
            $ip,
            $ua
        );

        return $after;
    }

    /**
     * Batch penerimaan dokumen fisik (1 SLS / 10 dokumen sekaligus atau pilihan ruta).
     * RBAC: hanya ADMIN, OPERATOR, dan SM_PLS (Tim IPDS).
     * @param array{periode_id:int, nks?:string, ruta_ids?:int[], tgl_pengiriman?:string, ttd_sos?:string, ttd_ipds?:string} $params
     */
    public function batchTerimaDokumen(array $params, array $currentUser): int
    {
        // RBAC: penerimaan dokumen fisik = mutasi status dokumen fisik → hanya ADMIN/OPERATOR/SM_PLS (Tim IPDS).
        $this->assertCanEditDocument($currentUser);

        $periodeId = (int) ($params['periode_id'] ?? 0);
        $nks = trim((string) ($params['nks'] ?? ''));
        $rutaIds = $params['ruta_ids'] ?? [];

        $targetIds = [];
        if (!empty($rutaIds) && is_array($rutaIds)) {
            $targetIds = array_map('intval', $rutaIds);
        } elseif ($nks !== '' && $periodeId > 0) {
            $stmt = $this->pdo->prepare(
                'SELECT sr.id FROM sampel_ruta sr
                 JOIN sampel sp ON sp.id = sr.sampel_id
                 JOIN sls s ON s.id = sp.sls_id
                 WHERE sp.periode_id = :p AND s.nks = :nks'
            );
            $stmt->execute([':p' => $periodeId, ':nks' => $nks]);
            $targetIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        if (empty($targetIds)) {
            return 0;
        }

        $tglPengiriman = !empty($params['tgl_pengiriman']) ? trim((string)$params['tgl_pengiriman']) : date('Y-m-d');
        $customSos = !empty($params['ttd_sos']) ? trim((string)$params['ttd_sos']) : null;
        $customIpds = !empty($params['ttd_ipds']) ? trim((string)$params['ttd_ipds']) : null;

        $count = 0;
        $this->pdo->beginTransaction();
        try {
            $inPlaceholders = implode(',', array_fill(0, count($targetIds), '?'));
            $stmtRows = $this->pdo->prepare(
                "SELECT sr.id, sr.ttd_sos, sr.ttd_ipds, o_pml.nama AS nama_pml, o_peng.nama AS nama_pengolah
                 FROM sampel_ruta sr
                 JOIN sampel sp ON sp.id = sr.sampel_id
                 LEFT JOIN penugasan pg ON pg.sampel_id = sp.id
                 LEFT JOIN orang o_pml ON o_pml.id = pg.pml_id
                 LEFT JOIN orang o_peng ON o_peng.id = pg.pengolah_id
                 WHERE sr.id IN ($inPlaceholders)"
            );
            $stmtRows->execute($targetIds);
            $rows = $stmtRows->fetchAll(PDO::FETCH_ASSOC);

            $stmtUpdate = $this->pdo->prepare(
                'UPDATE sampel_ruta 
                 SET status_dokumen = "ADA",
                     tgl_pengiriman = :tgl,
                     ttd_sos = :sos,
                     ttd_ipds = :ipds
                 WHERE id = :id'
            );

            foreach ($rows as $r) {
                $id = (int) $r['id'];
                $sos = $customSos ?: (!empty($r['ttd_sos']) ? $r['ttd_sos'] : (!empty($r['nama_pml']) ? trim((string)$r['nama_pml']) . ' (Tim Sosial)' : 'Tim Sosial'));
                $ipds = $customIpds ?: (!empty($r['ttd_ipds']) ? $r['ttd_ipds'] : (!empty($currentUser['nama']) ? trim((string)$currentUser['nama']) . ' (Tim IPDS)' : (!empty($r['nama_pengolah']) ? trim((string)$r['nama_pengolah']) . ' (Tim IPDS)' : 'Tim IPDS')));

                $stmtUpdate->execute([
                    ':tgl' => $tglPengiriman,
                    ':sos' => mb_substr($sos, 0, 100),
                    ':ipds' => mb_substr($ipds, 0, 100),
                    ':id' => $id,
                ]);
                $count += $stmtUpdate->rowCount();
            }

            // Catat audit log untuk batch penerimaan dokumen
            $userId = (int) ($currentUser['id'] ?? 0);
            $this->auditRepo->log(
                $userId > 0 ? $userId : null,
                'UPDATE',
                'sampel_ruta',
                'batch_terima_dokumen',
                null,
                ['action' => 'batch_terima_dokumen', 'nks' => $nks, 'tgl' => $tglPengiriman, 'affected' => $count],
                (string) ($currentUser['ip'] ?? ''),
                (string) ($currentUser['user_agent'] ?? '')
            );

            $this->pdo->commit();
            return $count;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Batch update status transfer (K, KP, Seruti) untuk seluruh ruta dalam satu NKS atau daftar ID.
     * @param array{periode_id:int, nks?:string, ruta_ids?:int[], field:string, value:int} $params
     */
    public function batchTransfer(array $params, array $currentUser): int
    {
        // RBAC: transfer K/KP/Seruti = mutasi status → ADMIN, OPERATOR, SM_PLS, PENGAWAS_OLAH, atau PENGOLAH (binaan sendiri).
        $role = (string) ($currentUser['role_code'] ?? $currentUser['role'] ?? 'VIEWER');
        $orangId = (int) ($currentUser['orang_id'] ?? 0);
        if (!$this->canTransfer($currentUser)) {
            throw new RuntimeException(self::EDIT_DENIED_MESSAGE, self::HTTP_FORBIDDEN);
        }
        if ($role === 'PENGOLAH' && $orangId <= 0) {
            throw new RuntimeException(self::EDIT_DENIED_MESSAGE, self::HTTP_FORBIDDEN);
        }

        $field = (string) ($params['field'] ?? '');
        $allowedFields = ['status_transfer_k', 'status_transfer_kp', 'status_transfer_seruti'];
        if (!in_array($field, $allowedFields, true)) {
            throw new RuntimeException('Bidang transfer tidak valid.');
        }

        if ($field === 'status_transfer_seruti' && !$this->canTransferSeruti($currentUser)) {
            throw new RuntimeException('Akses ditolak: Hanya Operator, Pengawas Pengolahan, dan Admin yang berhak melakukan transfer Seruti.', self::HTTP_FORBIDDEN);
        }

        $val = !empty($params['value']) ? 1 : 0;
        $periodeId = (int) ($params['periode_id'] ?? 0);
        $nks = trim((string) ($params['nks'] ?? ''));
        $rutaIds = $params['ruta_ids'] ?? [];

        $targetIds = [];
        if (!empty($rutaIds) && is_array($rutaIds)) {
            $rawIds = array_map('intval', $rutaIds);
            if ($role === 'PENGOLAH' && $orangId > 0 && !empty($rawIds)) {
                $inPl = implode(',', array_fill(0, count($rawIds), '?'));
                $stmtCheck = $this->pdo->prepare(
                    "SELECT sr.id FROM sampel_ruta sr
                     JOIN sampel sp ON sp.id = sr.sampel_id
                     JOIN penugasan pg ON pg.sampel_id = sp.id
                     WHERE sr.id IN ($inPl) AND pg.pengolah_id = ?"
                );
                $p = $rawIds;
                $p[] = $orangId;
                $stmtCheck->execute($p);
                $targetIds = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);
            } else {
                $targetIds = $rawIds;
            }
        } elseif ($nks !== '' && $periodeId > 0) {
            $sqlNks = 'SELECT sr.id FROM sampel_ruta sr
                 JOIN sampel sp ON sp.id = sr.sampel_id
                 JOIN sls s ON s.id = sp.sls_id
                 LEFT JOIN penugasan pg ON pg.sampel_id = sp.id
                 WHERE sp.periode_id = :p AND s.nks = :nks';
            $prmNks = [':p' => $periodeId, ':nks' => $nks];
            if ($role === 'PENGOLAH' && $orangId > 0) {
                $sqlNks .= ' AND pg.pengolah_id = :own';
                $prmNks[':own'] = $orangId;
            }
            $stmt = $this->pdo->prepare($sqlNks);
            $stmt->execute($prmNks);
            $targetIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        if (empty($targetIds)) {
            return 0;
        }

        // Ambil info periode
        $stmtP = $this->pdo->prepare('SELECT id, jenis, tahun FROM periode WHERE id = :id LIMIT 1');
        $stmtP->execute([':id' => $periodeId]);
        $pInfo = $stmtP->fetch(PDO::FETCH_ASSOC);
        $pJenis = (string) ($pInfo['jenis'] ?? '');
        $pTahun = (int) ($pInfo['tahun'] ?? 2026);

        // Jika periode Seruti: hanya ruta yang sudah aktif dari Susenas yang boleh diproses transfer
        if (str_starts_with($pJenis, 'SERUTI')) {
            $inPl = implode(',', array_fill(0, count($targetIds), '?'));
            $stmtAktif = $this->pdo->prepare("
                SELECT sr.id FROM sampel_ruta sr
                JOIN sampel sp ON sp.id = sr.sampel_id
                WHERE sr.id IN ($inPl)
                  AND EXISTS (
                    SELECT 1 FROM sampel_ruta sus_sr
                    JOIN sampel sus_sp ON sus_sp.id = sus_sr.sampel_id
                    JOIN periode sus_p ON sus_p.id = sus_sp.periode_id
                    WHERE sus_sp.sls_id = sp.sls_id
                      AND sus_sr.no_urut_ruta = sr.no_urut_ruta
                      AND sus_p.tahun = {$pTahun}
                      AND sus_p.jenis LIKE 'SUSENAS_%'
                      AND sus_sr.status_transfer_seruti = 1
                      AND sus_sr.status_dokumen = 'ADA'
                      AND sus_sr.status_transfer_k = 1
                      AND sus_sr.status_transfer_kp = 1
                  )
            ");
            $stmtAktif->execute($targetIds);
            $targetIds = $stmtAktif->fetchAll(PDO::FETCH_COLUMN);
            if (empty($targetIds)) {
                throw new RuntimeException(
                    'Akses ditolak: Sampel Seruti untuk NKS ini belum aktif. Dokumen Susenas harus selesai diolah dan ditransfer ke Seruti terlebih dahulu.',
                    self::HTTP_FORBIDDEN
                );
            }
        }

        // Jika periode Susenas dan ingin transfer Seruti: hanya ruta yang selesai olah (dokumen ADA, transfer K=1, transfer KP=1) yang boleh ditransfer
        if (str_starts_with($pJenis, 'SUSENAS') && $field === 'status_transfer_seruti' && $val === 1) {
            $inPl = implode(',', array_fill(0, count($targetIds), '?'));
            $stmtOlah = $this->pdo->prepare("
                SELECT id FROM sampel_ruta
                WHERE id IN ($inPl)
                  AND status_dokumen = 'ADA'
                  AND status_transfer_k = 1
                  AND status_transfer_kp = 1
            ");
            $stmtOlah->execute($targetIds);
            $targetIds = $stmtOlah->fetchAll(PDO::FETCH_COLUMN);
            if (empty($targetIds)) {
                throw new RuntimeException(
                    'Kuesioner Susenas di NKS ini belum selesai diolah (Dokumen fisik harus ADA, serta Transfer K dan KP harus selesai) sebelum dapat ditransfer ke Seruti.',
                    self::HTTP_BAD_REQUEST
                );
            }
        }

        $count = 0;
        $this->pdo->beginTransaction();
        try {
            $sql = "UPDATE sampel_ruta SET `{$field}` = :val WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            foreach ($targetIds as $id) {
                $stmt->execute([':val' => $val, ':id' => $id]);
                $count += $stmt->rowCount();
            }

            // Catat audit log untuk batch transfer
            $userId = (int) ($currentUser['id'] ?? 0);
            $this->auditRepo->log(
                $userId > 0 ? $userId : null,
                'UPDATE',
                'sampel_ruta',
                'batch_' . $field,
                null,
                ['field' => $field, 'value' => $val, 'nks' => $nks, 'affected' => $count],
                (string) ($currentUser['ip'] ?? ''),
                (string) ($currentUser['user_agent'] ?? '')
            );

            $this->pdo->commit();
            return $count;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Ekspor Lembar Kerja Pengolahan Sampel ke format Excel multi-sheet (.xlsx).
     * Format dan sheet identik dengan `LK Pengolahan Sampel (2).xlsx`.
     */
    public function exportLkExcel(int $periodeId): Spreadsheet
    {
        // 1. Ambil data master periode
        $stmtP = $this->pdo->prepare('SELECT id, label, tahun, jenis FROM periode WHERE id = :id');
        $stmtP->execute([':id' => $periodeId]);
        $periode = $stmtP->fetch(PDO::FETCH_ASSOC);
        if (!$periode) {
            throw new RuntimeException('Periode tidak ditemukan.');
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // hapus sheet default

        // 2. SHEET 1: Alokasi
        $sheetAlokasi = $spreadsheet->createSheet();
        $sheetAlokasi->setTitle('Alokasi');
        $headersAlokasi = [
            'NKS', 'Kecamatan', '', 'Desa / Kelurahan / Nagari', '',
            'PCL', 'No.HP PCL', 'PML', 'No.Hp PML', 'PENGOLAH', 'No.HP Pengolah'
        ];
        $sheetAlokasi->fromArray($headersAlokasi, null, 'A1');

        $stmtAlokasi = $this->pdo->prepare(
            'SELECT sl.nks, k.kode AS kode_kecamatan, k.nama AS nama_kecamatan,
                    d.kode AS kode_desa, d.nama AS nama_desa,
                    o_pcl.nama AS nama_pcl, o_pcl.no_hp AS hp_pcl,
                    o_pml.nama AS nama_pml, o_pml.no_hp AS hp_pml,
                    o_peng.nama AS nama_pengolah, o_peng.no_hp AS hp_pengolah
             FROM sampel sp
             JOIN sls sl ON sl.id = sp.sls_id
             LEFT JOIN desa d ON d.id = sl.desa_id
             LEFT JOIN kecamatan k ON k.kode = d.kecamatan_kode
             LEFT JOIN penugasan pg ON pg.sampel_id = sp.id
             LEFT JOIN orang o_pcl ON o_pcl.id = pg.pcl_id
             LEFT JOIN orang o_pml ON o_pml.id = pg.pml_id
             LEFT JOIN orang o_peng ON o_peng.id = pg.pengolah_id
             WHERE sp.periode_id = :p
             ORDER BY sl.nks ASC'
        );
        $stmtAlokasi->execute([':p' => $periodeId]);
        $alokasiRows = $stmtAlokasi->fetchAll(PDO::FETCH_ASSOC);

        $rowIdx = 2;
        $alokasiNksIndex = [];
        foreach ($alokasiRows as $ar) {
            $sheetAlokasi->fromArray([
                $ar['nks'],
                $ar['kode_kecamatan'],
                $ar['nama_kecamatan'],
                $ar['kode_desa'],
                $ar['nama_desa'],
                $ar['nama_pcl'] ?? '',
                $ar['hp_pcl'] ?? '',
                $ar['nama_pml'] ?? '',
                $ar['hp_pml'] ?? '',
                $ar['nama_pengolah'] ?? '',
                $ar['hp_pengolah'] ?? ''
            ], null, 'A' . $rowIdx);
            $alokasiNksIndex[$ar['nks']] = $rowIdx;
            $rowIdx++;
        }

        // 3. SHEET 2: Rekap
        $sheetRekap = $spreadsheet->createSheet();
        $sheetRekap->setTitle('Rekap');
        $headersRekap = [
            'No.', 'NKS', 'Kecamatan', 'Desa', 'No. Ruta', 'Status Dokumen',
            'Tanggal Terima', 'Diserahkan Oleh (Tim Sosial)', 'Diterima Oleh (Tim IPDS)',
            'PCL', 'PML', 'PENGOLAH', 'STATUS TRANSFER K', 'STATUS TRANSFER KP', 'STATUS TRANSFER SERUTI'
        ];
        $sheetRekap->fromArray($headersRekap, null, 'A1');

        $allRuta = $this->rutaRepo->getPengolahanRuta($periodeId, []);
        $rIdx = 2;
        foreach ($allRuta as $r) {
            $sheetRekap->fromArray([
                $rIdx - 1,
                $r['nks'],
                $r['nama_kecamatan'],
                $r['nama_desa'],
                $r['no_urut_ruta'],
                $r['status_dokumen'] === 'ADA' ? 'Ada' : '',
                $r['tgl_pengiriman'] ?? '',
                $r['ttd_sos'] ?? '',
                $r['ttd_ipds'] ?? '',
                $r['nama_pcl'] ?? '',
                $r['nama_pml'] ?? '',
                $r['nama_pengolah'] ?? '',
                $r['status_transfer_k'] ? 1 : '',
                $r['status_transfer_kp'] ? 1 : '',
                $r['status_transfer_seruti'] ? 1 : '',
            ], null, 'A' . $rIdx);
            $rIdx++;
        }

        // 4. SHEET 3: Jadwal Pengawas Pengolahan (terisi dari DB, format otomatis)
        $sheetJadwal = $spreadsheet->createSheet();
        $sheetJadwal->setTitle('Jadwal Pengawas Pengolahan');
        $headersJadwal = ['No', 'Hari', 'Tanggal', 'Bulan', 'Tahun', 'Nama Pengawas Pengolahan', 'Status'];
        $sheetJadwal->fromArray($headersJadwal, null, 'A1');
        $sheetJadwal->getStyle('A1:G1')->getFont()->setBold(true);
        $sheetJadwal->getStyle('A1:G1')->getFont()->getColor()->setRGB('FFFFFF');
        $sheetJadwal->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2E3450');
        $sheetJadwal->getStyle('A1:G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheetJadwal->freezePane('A2');
        $bulanId = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $jadwalRows = $this->getJadwalPengawas($periodeId);
        $jIdx = 2;
        foreach ($jadwalRows as $i => $j) {
            $dt = new \DateTimeImmutable((string) $j['tanggal']);
            $namaTugas = ($j['status'] ?? '') === 'LIBUR' ? '-' : (string) ($j['nama_pengawas'] ?? $j['orang_nama'] ?? '');
            $sheetJadwal->fromArray([
                $i + 1,
                (string) ($j['hari'] ?? ''),
                (int) $dt->format('d'),
                $bulanId[(int) $dt->format('n')] ?? $dt->format('m'),
                (int) $dt->format('Y'),
                $namaTugas,
                (string) ($j['status'] ?? ''),
            ], null, 'A' . $jIdx);
            if (($j['status'] ?? '') === 'LIBUR') {
                $sheetJadwal->getStyle('A' . $jIdx . ':G' . $jIdx)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
                $sheetJadwal->getStyle('A' . $jIdx . ':G' . $jIdx)->getFont()->setItalic(true)->getColor()->setRGB('888888');
            }
            $jIdx++;
        }
        $lastJadwal = max($jIdx - 1, 1);
        $sheetJadwal->getStyle('A1:G' . $lastJadwal)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheetJadwal->getStyle('A1:G' . $lastJadwal)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        foreach ([6, 14, 10, 12, 8, 32, 12] as $ci => $w) {
            $sheetJadwal->getColumnDimension(Coordinate::stringFromColumnIndex($ci + 1))->setWidth($w);
        }

        // 5. SHEET 4..N: Sheet per Petugas Pengolah
        $stmtPengolah = $this->pdo->prepare(
            'SELECT DISTINCT o.id, o.nama
             FROM penugasan pg
             JOIN sampel sp ON sp.id = pg.sampel_id
             JOIN orang o ON o.id = pg.pengolah_id
             WHERE sp.periode_id = :p
             ORDER BY o.nama ASC'
        );
        $stmtPengolah->execute([':p' => $periodeId]);
        $pengolahList = $stmtPengolah->fetchAll(PDO::FETCH_ASSOC);

        $headersPengolah = [
            'No.', 'NKS', 'No. Ruta', 'Status',
            'Keterangan KP (Pengolah)', 'Keterangan KP (PCL/PML)', 'Keterangan KP (Tim Sosial)',
            'Keputusan KP (Tim IPDS)',
            'Keterangan M (Pengolah)', 'Keterangan M (PCL/PML)', 'Keterangan M (Tim Sosial)',
            'Keputusan M (Tim IPDS)',
            'Uji Petik Pengawas Pengolahan', 'PCL', 'PML', 'PENGOLAH'
        ];

        foreach ($pengolahList as $p) {
            $pNama = trim($p['nama']);
            // Excel sheet name max 31 chars
            $sheetTitle = mb_substr($pNama, 0, 31);
            $sheetP = $spreadsheet->createSheet();
            $sheetP->setTitle($sheetTitle);
            $sheetP->fromArray($headersPengolah, null, 'A1');

            $pRouteList = $this->rutaRepo->getPengolahanRuta($periodeId, ['pengolah_id' => (int) $p['id']]);
            $pRowIdx = 2;
            foreach ($pRouteList as $pr) {
                $sheetP->fromArray([
                    $pRowIdx - 1,
                    $pr['nks'],
                    $pr['no_urut_ruta'],
                    $pr['status_selesai'],
                    $pr['ket_kp_pengolah'] ?? '',
                    $pr['ket_kp_lapangan'] ?? '',
                    $pr['ket_kp_sosial'] ?? '',
                    $pr['ket_kp_ipds'] ?? '',
                    $pr['ket_m_pengolah'] ?? '',
                    $pr['ket_m_lapangan'] ?? '',
                    $pr['ket_m_sosial'] ?? '',
                    $pr['ket_m_ipds'] ?? '',
                    $pr['uji_petik_pengawas'] ?? '',
                    $pr['nama_pcl'] ?? '',
                    $pr['nama_pml'] ?? '',
                    $pr['nama_pengolah'] ?? ''
                ], null, 'A' . $pRowIdx);
                $pRowIdx++;
            }
        }

        return $spreadsheet;
    }

    /**
     * Sinkronkan update dari file LK Excel ke database (status dokumen, status transfer, dan catatan error).
     * @return array{rekap_updated:int, catatan_updated:int}
     */
    public function importLkExcel(int $periodeId, string $filePath, array $currentUser): array
    {
        // RBAC: sinkronisasi Excel = mutasi massal → hanya ADMIN/OPERATOR/SM_PLS.
        $this->assertCanEdit($currentUser);

        if (!file_exists($filePath)) {
            throw new RuntimeException('File Excel tidak ditemukan.');
        }

        // Ambil peta NKS ke sampel_id
        $stmtSampel = $this->pdo->prepare(
            'SELECT sl.nks, sp.id AS sampel_id
             FROM sampel sp
             JOIN sls sl ON sl.id = sp.sls_id
             WHERE sp.periode_id = :p'
        );
        $stmtSampel->execute([':p' => $periodeId]);
        $sampelMap = [];
        foreach ($stmtSampel->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $sampelMap[trim($row['nks'])] = (int) $row['sampel_id'];
        }

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        $this->pdo->beginTransaction();
        $updatedRekap = 0;
        $updatedCatatan = 0;

        try {
            // 1. Baca Sheet 'Rekap'
            $rekapSheet = $spreadsheet->getSheetByName('Rekap');
            if ($rekapSheet) {
                $col7Header = strtolower(trim((string)$rekapSheet->getCell([7, 1])->getValue()));
                $hasReceiptCols = str_contains($col7Header, 'terima');

                $hRow = $rekapSheet->getHighestRow();
                for ($r = 2; $r <= $hRow; $r++) {
                    $nks = trim((string)$rekapSheet->getCell([2, $r])->getValue());
                    $noRuta = (int) $rekapSheet->getCell([5, $r])->getValue();
                    if ($nks === '' || $noRuta < 1 || $noRuta > 10 || !isset($sampelMap[$nks])) {
                        continue;
                    }
                    $sampelId = $sampelMap[$nks];

                    $sDokRaw = strtolower(trim((string)$rekapSheet->getCell([6, $r])->getValue()));
                    $sDok = ($sDokRaw === 'ada') ? 'ADA' : 'BELUM';

                    if ($hasReceiptCols) {
                        $tglTerimaRaw = trim((string)$rekapSheet->getCell([7, $r])->getValue());
                        $tglTerima = ($tglTerimaRaw !== '') ? $tglTerimaRaw : ($sDok === 'ADA' ? date('Y-m-d') : null);
                        $ttdSos = trim((string)$rekapSheet->getCell([8, $r])->getValue());
                        $ttdIpds = trim((string)$rekapSheet->getCell([9, $r])->getValue());
                        $stK = (trim((string)$rekapSheet->getCell([13, $r])->getValue()) === '1') ? 1 : 0;
                        $stKP = (trim((string)$rekapSheet->getCell([14, $r])->getValue()) === '1') ? 1 : 0;
                        $stSeruti = (trim((string)$rekapSheet->getCell([15, $r])->getValue()) === '1') ? 1 : 0;

                        $stmtU = $this->pdo->prepare(
                            "UPDATE sampel_ruta
                             SET status_dokumen = :sdok,
                                 tgl_pengiriman = :tgl,
                                 ttd_sos = :sos,
                                 ttd_ipds = :ipds,
                                 status_transfer_k = :stk,
                                 status_transfer_kp = :stkp,
                                 status_transfer_seruti = :stser
                             WHERE sampel_id = :sid AND no_urut_ruta = :no"
                        );
                        $stmtU->execute([
                            ':sdok' => $sDok,
                            ':tgl' => $tglTerima,
                            ':sos' => $ttdSos !== '' ? mb_substr($ttdSos, 0, 100) : null,
                            ':ipds' => $ttdIpds !== '' ? mb_substr($ttdIpds, 0, 100) : null,
                            ':stk' => $stK,
                            ':stkp' => $stKP,
                            ':stser' => $stSeruti,
                            ':sid' => $sampelId,
                            ':no' => $noRuta,
                        ]);
                    } else {
                        $stK = (trim((string)$rekapSheet->getCell([10, $r])->getValue()) === '1') ? 1 : 0;
                        $stKP = (trim((string)$rekapSheet->getCell([11, $r])->getValue()) === '1') ? 1 : 0;
                        $stSeruti = (trim((string)$rekapSheet->getCell([12, $r])->getValue()) === '1') ? 1 : 0;

                        $stmtU = $this->pdo->prepare(
                            "UPDATE sampel_ruta
                             SET status_dokumen = :sdok,
                                 status_transfer_k = :stk,
                                 status_transfer_kp = :stkp,
                                 status_transfer_seruti = :stser
                             WHERE sampel_id = :sid AND no_urut_ruta = :no"
                        );
                        $stmtU->execute([
                            ':sdok' => $sDok,
                            ':stk' => $stK,
                            ':stkp' => $stKP,
                            ':stser' => $stSeruti,
                            ':sid' => $sampelId,
                            ':no' => $noRuta,
                        ]);
                    }
                    $updatedRekap++;
                }
            }

            // 2. Baca sheet individual pengolah
            $updateCatatanStmt = $this->pdo->prepare(
                'UPDATE sampel_ruta
                 SET catatan_kp = COALESCE(:ckp, catatan_kp),
                     ket_kp_pengolah = :kp_pengolah,
                     ket_kp_lapangan = :kp_lap,
                     ket_kp_sosial = :kp_sos,
                     ket_kp_ipds = :kp_ipds,
                     catatan_modul = COALESCE(:cmod, catatan_modul),
                     ket_m_pengolah = :m_pengolah,
                     ket_m_lapangan = :m_lap,
                     ket_m_sosial = :m_sos,
                     ket_m_ipds = :m_ipds,
                     uji_petik_pengawas = :uji
                 WHERE sampel_id = :sid AND no_urut_ruta = :no'
            );

            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                if (in_array($sheetName, ['Alokasi', 'Rekap', 'Jadwal Pengawas Pengolahan', 'Master', 'Sheet7'], true)) {
                    continue;
                }
                $sheet = $spreadsheet->getSheetByName($sheetName);
                if (!$sheet) continue;

                // Deteksi layout: file baru memuat kolom "Keputusan ... (Tim IPDS)"
                // di kolom 8 & 12; file lama (tanpa IPDS) tetap dibaca dgn indeks lama.
                $col8Header = strtolower(trim((string)$sheet->getCell([8, 1])->getValue()));
                $hasIpdsCols = str_contains($col8Header, 'ipds');
                $cKpIpds = $hasIpdsCols ? 8 : 0;
                $cMPengolah = $hasIpdsCols ? 9 : 8;
                $cMLap = $hasIpdsCols ? 10 : 9;
                $cMSos = $hasIpdsCols ? 11 : 10;
                $cMIpds = $hasIpdsCols ? 12 : 0;
                $cUji = $hasIpdsCols ? 13 : 11;

                $pRows = $sheet->getHighestRow();
                for ($r = 2; $r <= $pRows; $r++) {
                    $nks = trim((string)$sheet->getCell([2, $r])->getValue());
                    $noRuta = (int) $sheet->getCell([3, $r])->getValue();
                    if ($nks === '' || $noRuta < 1 || $noRuta > 10 || !isset($sampelMap[$nks])) {
                        continue;
                    }
                    $sampelId = $sampelMap[$nks];

                    $kpPengolah = trim((string)$sheet->getCell([5, $r])->getValue());
                    $kpLap = trim((string)$sheet->getCell([6, $r])->getValue());
                    $kpSos = trim((string)$sheet->getCell([7, $r])->getValue());
                    $kpIpds = $cKpIpds > 0 ? trim((string)$sheet->getCell([$cKpIpds, $r])->getValue()) : '';
                    $mPengolah = trim((string)$sheet->getCell([$cMPengolah, $r])->getValue());
                    $mLap = trim((string)$sheet->getCell([$cMLap, $r])->getValue());
                    $mSos = trim((string)$sheet->getCell([$cMSos, $r])->getValue());
                    $mIpds = $cMIpds > 0 ? trim((string)$sheet->getCell([$cMIpds, $r])->getValue()) : '';
                    $uji = trim((string)$sheet->getCell([$cUji, $r])->getValue());

                    if ($kpPengolah !== '' || $kpLap !== '' || $kpSos !== '' || $kpIpds !== '' ||
                        $mPengolah !== '' || $mLap !== '' || $mSos !== '' || $mIpds !== '' || $uji !== '') {
                        $updateCatatanStmt->execute([
                            ':ckp' => ($kpPengolah !== '') ? 1 : null,
                            ':kp_pengolah' => $kpPengolah !== '' ? $kpPengolah : null,
                            ':kp_lap' => $kpLap !== '' ? $kpLap : null,
                            ':kp_sos' => $kpSos !== '' ? $kpSos : null,
                            ':kp_ipds' => $kpIpds !== '' ? $kpIpds : null,
                            ':cmod' => ($mPengolah !== '') ? 1 : null,
                            ':m_pengolah' => $mPengolah !== '' ? $mPengolah : null,
                            ':m_lap' => $mLap !== '' ? $mLap : null,
                            ':m_sos' => $mSos !== '' ? $mSos : null,
                            ':m_ipds' => $mIpds !== '' ? $mIpds : null,
                            ':uji' => $uji !== '' ? $uji : null,
                            ':sid' => $sampelId,
                            ':no' => $noRuta,
                        ]);
                        $updatedCatatan++;
                    }
                }
            }

            $this->pdo->commit();

            // Catat audit import
            $userId = (int) ($currentUser['id'] ?? 0);
            $ip = (string) ($currentUser['ip'] ?? '');
            $ua = (string) ($currentUser['user_agent'] ?? '');
            $this->auditRepo->log(
                $userId > 0 ? $userId : null,
                'IMPORT',
                'sampel_ruta',
                (string) $periodeId,
                null,
                ['updated_rekap' => $updatedRekap, 'updated_catatan' => $updatedCatatan],
                $ip,
                $ua
            );

            return [
                'updated_rekap' => $updatedRekap,
                'updated_catatan' => $updatedCatatan,
            ];
        } catch (\Throwable $t) {
            $this->pdo->rollBack();
            throw $t;
        }
    }
}
