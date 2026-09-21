<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditRepository;
use App\Repositories\PeriodeRepository;
use App\Repositories\SampelRutaRepository;
use PDO;
use RuntimeException;

final class SerutiService
{
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_BAD_REQUEST = 400;

    public function __construct(
        private PDO $pdo,
        private SampelRutaRepository $rutaRepo,
        private PeriodeRepository $periodeRepo,
        private AuditRepository $auditRepo
    ) {
    }

    /**
     * Hak akses transfer Seruti: hanya ADMIN, OPERATOR, SM_PLS, PENGAWAS_OLAH.
     * PENGOLAH DILARANG MUTLAK.
     */
    public function canTransferSeruti(array $user): bool
    {
        $role = (string) ($user['role_code'] ?? $user['role'] ?? 'VIEWER');
        return in_array($role, ['ADMIN', 'OPERATOR', 'SM_PLS', 'PENGAWAS_OLAH'], true);
    }

    /**
     * Hak akses tulis modul Seruti: ADMIN, OPERATOR, SM_PLS, PENGOLAH, PENGAWAS_OLAH.
     * PENGOLAH hanya bisa tulis data binaan sendiri + catatan error & transfer (bukan transfer Seruti).
     */
    public function canEdit(array $user): bool
    {
        $role = (string) ($user['role_code'] ?? $user['role'] ?? 'VIEWER');
        return in_array($role, ['ADMIN', 'OPERATOR', 'SM_PLS', 'PENGOLAH', 'PENGAWAS_OLAH'], true);
    }

    /**
     * Daftar periode Seruti (SERUTI_Q1 s/d SERUTI_Q4).
     * @return array<int,array<string,mixed>>
     */
    public function getSerutiPeriodes(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM periode WHERE jenis LIKE 'SERUTI_%' ORDER BY jenis ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Periode aktif Seruti (bawaan SERUTI_Q3 jika tidak ada parameter).
     */
    public function getDefaultSerutiPeriodeId(int $overrideId = 0): int
    {
        if ($overrideId > 0) {
            $p = $this->periodeRepo->find($overrideId);
            if ($p !== null && str_starts_with((string) ($p['jenis'] ?? ''), 'SERUTI')) {
                return (int) $p['id'];
            }
        }

        $periodes = $this->getSerutiPeriodes();
        foreach ($periodes as $p) {
            if (($p['status'] ?? '') === 'AKTIF' && (string) ($p['jenis'] ?? '') === 'SERUTI_Q3') {
                return (int) $p['id'];
            }
        }
        foreach ($periodes as $p) {
            if (($p['status'] ?? '') === 'AKTIF') {
                return (int) $p['id'];
            }
        }
        if ($periodes !== []) {
            return (int) $periodes[0]['id'];
        }
        return 0;
    }

    /**
     * Scoping pengguna: PENGOLAH dibatasi pada ruta binaan sendiri.
     * Return pengolah_id untuk filter, atau -1 jika tidak ada scope.
     */
    public function getPengolahScope(array $user): int
    {
        $role = (string) ($user['role_code'] ?? $user['role'] ?? 'VIEWER');
        if ($role === 'PENGOLAH') {
            return (int) ($user['orang_id'] ?? 0);
        }
        return -1;
    }

    /**
     * Ambil daftar ruta Seruti dengan filter & scoping.
     * @param array{pengolah_id?:int,status_kesiapan?:string,pengolah_nama?:string,q?:string} $filters
     * @return array<int,array<string,mixed>>
     */
    public function getDaftarRuta(int $periodeId, array $filters, array $currentUser): array
    {
        $pengolahScope = $this->getPengolahScope($currentUser);

        $sql = "SELECT sr.*, sp.periode_id, p.jenis AS periode_jenis, p.tahun AS periode_tahun,
                       sl.nks, sl.nama_sls, sl.kode_full,
                       d.nama AS nama_desa, k.nama AS nama_kecamatan,
                       pg.pcl_id, o_pcl.nama AS nama_pcl, o_pcl.no_hp AS hp_pcl,
                       pg.pml_id, o_pml.nama AS nama_pml, o_pml.no_hp AS hp_pml,
                       pg.pengolah_id, o_peng.nama AS nama_pengolah, o_peng.no_hp AS hp_pengolah,
                       CASE
                         WHEN p.jenis NOT LIKE 'SERUTI_%' THEN 1
                         WHEN EXISTS (
                           SELECT 1 FROM sampel_ruta sus_sr
                           JOIN sampel sus_sp ON sus_sp.id = sus_sr.sampel_id
                           JOIN periode sus_p ON sus_p.id = sus_sp.periode_id
                           WHERE sus_sp.sls_id = sp.sls_id
                             AND sus_sr.no_urut_ruta = sr.no_urut_ruta
                             AND sus_p.tahun = p.tahun
                             AND sus_p.jenis LIKE 'SUSENAS_%'
                             AND sus_sr.status_transfer_seruti = 1
                             AND sus_sr.status_dokumen = 'ADA'
                             AND sus_sr.status_transfer_k = 1
                             AND sus_sr.status_transfer_kp = 1
                         ) THEN 1
                         ELSE 0
                       END AS is_seruti_aktif
                FROM sampel_ruta sr
                JOIN sampel sp ON sp.id = sr.sampel_id
                JOIN periode p ON p.id = sp.periode_id
                JOIN sls sl ON sl.id = sp.sls_id
                LEFT JOIN desa d ON d.id = sl.desa_id
                LEFT JOIN kecamatan k ON k.kode = d.kecamatan_kode
                LEFT JOIN penugasan pg ON pg.sampel_id = sp.id
                LEFT JOIN orang o_pcl ON o_pcl.id = pg.pcl_id
                LEFT JOIN orang o_pml ON o_pml.id = pg.pml_id
                LEFT JOIN orang o_peng ON o_peng.id = pg.pengolah_id
                WHERE sp.periode_id = :p AND p.jenis LIKE 'SERUTI_%'";
        $params = [':p' => $periodeId];

        if ($pengolahScope > 0) {
            $sql .= ' AND pg.pengolah_id = :pengolah_id';
            $params[':pengolah_id'] = $pengolahScope;
        }

        $statusKesiapan = $filters['status_kesiapan'] ?? null;
        if ($statusKesiapan === 'siap') {
            $sql .= ' AND EXISTS (
                SELECT 1 FROM sampel_ruta sus_sr
                JOIN sampel sus_sp ON sus_sp.id = sus_sr.sampel_id
                JOIN periode sus_p ON sus_p.id = sus_sp.periode_id
                WHERE sus_sp.sls_id = sp.sls_id
                  AND sus_sr.no_urut_ruta = sr.no_urut_ruta
                  AND sus_p.tahun = p.tahun
                  AND sus_p.jenis LIKE "SUSENAS_%"
                  AND sus_sr.status_transfer_seruti = 1
                  AND sus_sr.status_dokumen = "ADA"
                  AND sus_sr.status_transfer_k = 1
                  AND sus_sr.status_transfer_kp = 1
            )';
        } elseif ($statusKesiapan === 'menunggu') {
            $sql .= ' AND NOT EXISTS (
                SELECT 1 FROM sampel_ruta sus_sr
                JOIN sampel sus_sp ON sus_sp.id = sus_sr.sampel_id
                JOIN periode sus_p ON sus_p.id = sus_sp.periode_id
                WHERE sus_sp.sls_id = sp.sls_id
                  AND sus_sr.no_urut_ruta = sr.no_urut_ruta
                  AND sus_p.tahun = p.tahun
                  AND sus_p.jenis LIKE "SUSENAS_%"
                  AND sus_sr.status_transfer_seruti = 1
                  AND sus_sr.status_dokumen = "ADA"
                  AND sus_sr.status_transfer_k = 1
                  AND sus_sr.status_transfer_kp = 1
            )';
        }

        if (!empty($filters['pengolah_nama'])) {
            $sql .= ' AND o_peng.nama LIKE :pengolah_nama';
            $params[':pengolah_nama'] = '%' . $filters['pengolah_nama'] . '%';
        }

        if (!empty($filters['q'])) {
            $sql .= ' AND (sl.nks LIKE :q OR sl.nama_sls LIKE :q2 OR d.nama LIKE :q3 OR k.nama LIKE :q4)';
            $params[':q'] = '%' . $filters['q'] . '%';
            $params[':q2'] = '%' . $filters['q'] . '%';
            $params[':q3'] = '%' . $filters['q'] . '%';
            $params[':q4'] = '%' . $filters['q'] . '%';
        }

        $sql .= ' ORDER BY sl.nks ASC, sr.no_urut_ruta ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ringkasan KPI Seruti.
     * @return array{total_sampel:int, susenas_selesai:int, susenas_selesai_pct:float, siap_olah:int, terkunci:int, selesai_transfer:int}
     */
    public function getKpi(int $periodeId, int $pengolahId = -1): array
    {
        $joinAndParams = $this->buildScopeJoin($pengolahId);
        $sql = "SELECT
                    COUNT(*) AS total_sampel,
                    SUM(CASE WHEN EXISTS (
                        SELECT 1 FROM sampel_ruta sus_sr
                        JOIN sampel sus_sp ON sus_sp.id = sus_sr.sampel_id
                        JOIN periode sus_p ON sus_p.id = sus_sp.periode_id
                        WHERE sus_sp.sls_id = sp.sls_id
                          AND sus_sr.no_urut_ruta = sr.no_urut_ruta
                          AND sus_p.tahun = p.tahun
                          AND sus_p.jenis LIKE 'SUSENAS_%'
                          AND sus_sr.status_transfer_seruti = 1
                          AND sus_sr.status_dokumen = 'ADA'
                          AND sus_sr.status_transfer_k = 1
                          AND sus_sr.status_transfer_kp = 1
                    ) THEN 1 ELSE 0 END) AS susenas_selesai,
                    SUM(CASE WHEN EXISTS (
                        SELECT 1 FROM sampel_ruta sus_sr
                        JOIN sampel sus_sp ON sus_sp.id = sus_sr.sampel_id
                        JOIN periode sus_p ON sus_p.id = sus_sp.periode_id
                        WHERE sus_sp.sls_id = sp.sls_id
                          AND sus_sr.no_urut_ruta = sr.no_urut_ruta
                          AND sus_p.tahun = p.tahun
                          AND sus_p.jenis LIKE 'SUSENAS_%'
                          AND sus_sr.status_transfer_seruti = 1
                          AND sus_sr.status_dokumen = 'ADA'
                          AND sus_sr.status_transfer_k = 1
                          AND sus_sr.status_transfer_kp = 1
                    ) THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(*), 0) AS susenas_selesai_pct,
                    SUM(CASE WHEN EXISTS (
                        SELECT 1 FROM sampel_ruta sus_sr
                        JOIN sampel sus_sp ON sus_sp.id = sus_sr.sampel_id
                        JOIN periode sus_p ON sus_p.id = sus_sp.periode_id
                        WHERE sus_sp.sls_id = sp.sls_id
                          AND sus_sr.no_urut_ruta = sr.no_urut_ruta
                          AND sus_p.tahun = p.tahun
                          AND sus_p.jenis LIKE 'SUSENAS_%'
                          AND sus_sr.status_transfer_seruti = 1
                          AND sus_sr.status_dokumen = 'ADA'
                          AND sus_sr.status_transfer_k = 1
                          AND sus_sr.status_transfer_kp = 1
                    ) THEN 1 ELSE 0 END) AS siap_olah,
                    SUM(CASE WHEN NOT EXISTS (
                        SELECT 1 FROM sampel_ruta sus_sr
                        JOIN sampel sus_sp ON sus_sp.id = sus_sr.sampel_id
                        JOIN periode sus_p ON sus_p.id = sus_sp.periode_id
                        WHERE sus_sp.sls_id = sp.sls_id
                          AND sus_sr.no_urut_ruta = sr.no_urut_ruta
                          AND sus_p.tahun = p.tahun
                          AND sus_p.jenis LIKE 'SUSENAS_%'
                          AND sus_sr.status_transfer_seruti = 1
                          AND sus_sr.status_dokumen = 'ADA'
                          AND sus_sr.status_transfer_k = 1
                          AND sus_sr.status_transfer_kp = 1
                    ) THEN 1 ELSE 0 END) AS terkunci,
                    SUM(CASE WHEN sr.status_transfer_seruti = 1 THEN 1 ELSE 0 END) AS selesai_transfer
                FROM sampel_ruta sr
                JOIN sampel sp ON sp.id = sr.sampel_id
                JOIN periode p ON p.id = sp.periode_id
                {$joinAndParams['join']}
                WHERE sp.periode_id = :p AND p.jenis LIKE 'SERUTI_%'";

        $params = $joinAndParams['params'];
        $params[':p'] = $periodeId;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return ['total_sampel' => 0, 'susenas_selesai' => 0, 'susenas_selesai_pct' => 0.0, 'siap_olah' => 0, 'terkunci' => 0, 'selesai_transfer' => 0];
        }
        return [
            'total_sampel' => (int) ($row['total_sampel'] ?? 0),
            'susenas_selesai' => (int) ($row['susenas_selesai'] ?? 0),
            'susenas_selesai_pct' => (float) ($row['susenas_selesai_pct'] ?? 0),
            'siap_olah' => (int) ($row['siap_olah'] ?? 0),
            'terkunci' => (int) ($row['terkunci'] ?? 0),
            'selesai_transfer' => (int) ($row['selesai_transfer'] ?? 0),
        ];
    }

    /**
     * Hitung jumlah NKS unik di periode Seruti (dengan scoping).
     */
    public function countNKS(int $periodeId, int $pengolahId = -1): int
    {
        $joinAndParams = $this->buildScopeJoin($pengolahId);
        $sql = "SELECT COUNT(DISTINCT sl.nks) AS nks_count
                FROM sampel_ruta sr
                JOIN sampel sp ON sp.id = sr.sampel_id
                JOIN periode p ON p.id = sp.periode_id
                JOIN sls sl ON sl.id = sp.sls_id
                {$joinAndParams['join']}
                WHERE sp.periode_id = :p AND p.jenis LIKE 'SERUTI_%'";
        $params = $joinAndParams['params'];
        $params[':p'] = $periodeId;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['nks_count'] ?? 0);
    }

    /**
     * Daftar NKS unik di periode Seruti (dengan scoping).
     * @return array<string>
     */
    public function getNKSList(int $periodeId, int $pengolahId = -1): array
    {
        $joinAndParams = $this->buildScopeJoin($pengolahId);
        $sql = "SELECT DISTINCT sl.nks
                FROM sampel_ruta sr
                JOIN sampel sp ON sp.id = sr.sampel_id
                JOIN periode p ON p.id = sp.periode_id
                JOIN sls sl ON sl.id = sp.sls_id
                {$joinAndParams['join']}
                WHERE sp.periode_id = :p AND p.jenis LIKE 'SERUTI_%'
                ORDER BY sl.nks ASC";
        $params = $joinAndParams['params'];
        $params[':p'] = $periodeId;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Daftar pengolah di periode (dengan scoping).
     * @return array<int,array<string,mixed>>
     */
    public function getPengolahList(int $periodeId, int $pengolahScope = -1): array
    {
        $sql = "SELECT DISTINCT o.id, o.nama
                FROM penugasan pg
                JOIN sampel sp ON sp.id = pg.sampel_id
                JOIN orang o ON o.id = pg.pengolah_id
                WHERE sp.periode_id = :p AND sp.periode_id IN (
                    SELECT id FROM periode WHERE jenis LIKE 'SERUTI_%'
                )";
        $params = [':p' => $periodeId];
        if ($pengolahScope > 0) {
            $sql .= ' AND pg.pengolah_id = :own';
            $params[':own'] = $pengolahScope;
        }
        $sql .= ' ORDER BY o.nama ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Hitung jumlah ruta milik pengolah tertentu di periode Seruti.
     */
    public function countRutaByPengolah(int $periodeId, int $pengolahId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) AS cnt FROM sampel_ruta sr
            JOIN sampel sp ON sp.id = sr.sampel_id
            JOIN periode p ON p.id = sp.periode_id
            JOIN penugasan pg ON pg.sampel_id = sp.id
            WHERE sp.periode_id = :p AND p.jenis LIKE 'SERUTI_%' AND pg.pengolah_id = :own
        ");
        $stmt->execute([':p' => $periodeId, ':own' => $pengolahId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['cnt'] ?? 0);
    }

    /**
     * Export data pengolahan Seruti ke spreadsheet.
     * @return array{headers:array<string>,rows:array<int,array<string,mixed>>}
     */
    public function exportData(int $periodeId, array $filters, array $currentUser): array
    {
        $rutaList = $this->getDaftarRuta($periodeId, $filters, $currentUser);

        $headers = [
            'No.', 'NKS', 'Kecamatan', 'Desa', 'No. Urut Ruta',
            'Petugas Pengolah', 'Petugas Pengawas',
            'Prasyarat Susenas', 'Status Seruti', 'Transfer Seruti',
            'Catatan Kendali Mutu Seruti',
        ];

        $rows = [];
        foreach ($rutaList as $idx => $r) {
            $susenasReady = (bool) ($r['is_seruti_aktif'] ?? false);
            $rows[] = [
                'no' => $idx + 1,
                'nks' => (string) ($r['nks'] ?? ''),
                'kecamatan' => (string) ($r['nama_kecamatan'] ?? ''),
                'desa' => (string) ($r['nama_desa'] ?? ''),
                'no_urut_ruta' => (int) ($r['no_urut_ruta'] ?? 0),
                'pengolah' => (string) ($r['nama_pengolah'] ?? ''),
                'pengawas' => '',
                'prasyarat_susenas' => $susenasReady ? 'Susenas Selesai' : 'Menunggu Susenas',
                'status_seruti' => $susenasReady ? 'Siap Olah' : 'Terkunci',
                'transfer_seruti' => (int) ($r['status_transfer_seruti'] ?? 0) ? 'Sudah' : 'Belum',
                'catatan' => '',
            ];
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * Validasi: apakah pengguna boleh mengubah status transfer Seruti pada ruta tertentu.
     * @throws RuntimeException dengan kode 403 jika dilarang
     */
    public function assertCanTransferSeruti(int $rutaId, array $currentUser): void
    {
        $role = (string) ($currentUser['role_code'] ?? $currentUser['role'] ?? 'VIEWER');

        if ($role === 'PENGOLAH') {
            throw new RuntimeException(
                'Akses ditolak: Petugas Pengolah Data tidak memiliki wewenang melakukan transfer Seruti.',
                self::HTTP_FORBIDDEN
            );
        }

        if (!$this->canTransferSeruti($currentUser)) {
            throw new RuntimeException(
                'Akses ditolak: Hanya Operator, Pengawas Pengolahan, dan Admin yang berhak melakukan transfer Seruti.',
                self::HTTP_FORBIDDEN
            );
        }

        $ruta = $this->rutaRepo->findRutaById($rutaId);
        if (!$ruta) {
            throw new RuntimeException('Data ruta tidak ditemukan.', self::HTTP_BAD_REQUEST);
        }

        if (str_starts_with((string) ($ruta['periode_jenis'] ?? ''), 'SERUTI')) {
            if (empty($ruta['is_seruti_aktif'])) {
                throw new RuntimeException(
                    'Akses ditolak: Sampel Seruti untuk NKS ini belum aktif. Dokumen Susenas harus selesai diolah dan ditransfer ke Seruti terlebih dahulu.',
                    self::HTTP_FORBIDDEN
                );
            }
        }
    }

    /**
     * Validasi: apakah pengguna boleh mengubah data pada ruta tertentu (update umum).
     * @throws RuntimeException
     */
    public function assertCanEditRuta(int $rutaId, array $currentUser): void
    {
        $role = (string) ($currentUser['role_code'] ?? $currentUser['role'] ?? 'VIEWER');
        $orangId = (int) ($currentUser['orang_id'] ?? 0);

        if (!$this->canEdit($currentUser)) {
            throw new RuntimeException('Akses ditolak: Anda tidak memiliki wewenang untuk mengubah data.', self::HTTP_FORBIDDEN);
        }

        $ruta = $this->rutaRepo->findRutaById($rutaId);
        if (!$ruta) {
            throw new RuntimeException('Data ruta tidak ditemukan.', self::HTTP_BAD_REQUEST);
        }

        if ($role === 'PENGOLAH') {
            if ($orangId <= 0 || (int) ($ruta['pengolah_id'] ?? 0) !== $orangId) {
                throw new RuntimeException('Akses ditolak: Anda hanya dapat mengubah data ruta binaan Anda sendiri.', self::HTTP_FORBIDDEN);
            }
        }

        $isSerutiPeriode = str_starts_with((string) ($ruta['periode_jenis'] ?? ''), 'SERUTI');
        if ($isSerutiPeriode && empty($ruta['is_seruti_aktif'])) {
            throw new RuntimeException(
                'Akses ditolak: Sampel Seruti untuk NKS ini belum aktif. Dokumen Susenas harus selesai diolah dan ditransfer ke Seruti terlebih dahulu.',
                self::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * Update data ruta Seruti.
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    public function updateRuta(int $rutaId, array $data, array $currentUser): array
    {
        $this->assertCanEditRuta($rutaId, $currentUser);

        $role = (string) ($currentUser['role_code'] ?? $currentUser['role'] ?? 'VIEWER');
        $orangId = (int) ($currentUser['orang_id'] ?? 0);
        $userId = (int) ($currentUser['id'] ?? 0);

        if (array_key_exists('status_transfer_seruti', $data)) {
            $this->assertCanTransferSeruti($rutaId, $currentUser);
        }

        if ($role === 'PENGOLAH') {
            if (array_key_exists('uji_petik_pengawas', $data)) {
                unset($data['uji_petik_pengawas']);
            }
            unset(
                $data['status_dokumen'], $data['tgl_pengiriman'], $data['ttd_sos'], $data['ttd_ipds'],
                $data['ket_kp_ipds'], $data['ket_m_ipds'], $data['ket_kp_sosial'], $data['ket_m_sosial'],
                $data['ket_kp_lapangan'], $data['ket_m_lapangan']
            );
        }

        $ruta = $this->rutaRepo->findRutaById($rutaId);
        $before = $ruta ?? [];
        $ok = $this->rutaRepo->updatePengolahanRow($rutaId, $data);
        if (!$ok) {
            throw new RuntimeException('Gagal memperbarui data ruta.');
        }

        $after = $this->rutaRepo->findRutaById($rutaId);

        $this->auditRepo->log(
            $userId > 0 ? $userId : null,
            'UPDATE',
            'sampel_ruta',
            (string) $rutaId,
            $before,
            $after,
            (string) ($currentUser['ip'] ?? ''),
            (string) ($currentUser['user_agent'] ?? '')
        );

        return $after ?? [];
    }

    /**
     * Batch transfer Seruti.
     * @param array{periode_id:int,nks?:string,ruta_ids?:int[]} $params
     * @return int
     */
    public function batchTransfer(array $params, array $currentUser): int
    {
        $role = (string) ($currentUser['role_code'] ?? $currentUser['role'] ?? 'VIEWER');

        if ($role === 'PENGOLAH') {
            throw new RuntimeException(
                'Akses ditolak: Petugas Pengolah Data tidak berhak melakukan transfer Seruti.',
                self::HTTP_FORBIDDEN
            );
        }
        if (!$this->canTransferSeruti($currentUser)) {
            throw new RuntimeException(
                'Akses ditolak: Hanya Operator, Pengawas Pengolahan, dan Admin yang berhak melakukan transfer Seruti.',
                self::HTTP_FORBIDDEN
            );
        }

        $field = 'status_transfer_seruti';
        $value = !empty($params['value']) ? 1 : 0;
        $periodeId = (int) ($params['periode_id'] ?? 0);
        $nks = trim((string) ($params['nks'] ?? ''));
        $rutaIds = $params['ruta_ids'] ?? [];

        $targetIds = [];
        if (!empty($rutaIds) && is_array($rutaIds)) {
            $targetIds = array_map('intval', $rutaIds);
        } elseif ($nks !== '' && $periodeId > 0) {
            $stmt = $this->pdo->prepare("
                SELECT sr.id FROM sampel_ruta sr
                JOIN sampel sp ON sp.id = sr.sampel_id
                JOIN sls s ON s.id = sp.sls_id
                WHERE sp.periode_id = :p AND s.nks = :nks
            ");
            $stmt->execute([':p' => $periodeId, ':nks' => $nks]);
            $targetIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        if (empty($targetIds)) {
            return 0;
        }

        if ($value === 1) {
            $inPl = implode(',', array_fill(0, count($targetIds), '?'));
            $stmtAktif = $this->pdo->prepare("
                SELECT sr.id FROM sampel_ruta sr
                JOIN sampel sp ON sp.id = sr.sampel_id
                JOIN periode p ON p.id = sp.periode_id
                WHERE sr.id IN ($inPl) AND p.jenis LIKE 'SERUTI_%'
                  AND EXISTS (
                    SELECT 1 FROM sampel_ruta sus_sr
                    JOIN sampel sus_sp ON sus_sp.id = sus_sr.sampel_id
                    JOIN periode sus_p ON sus_p.id = sus_sp.periode_id
                    WHERE sus_sp.sls_id = sp.sls_id
                      AND sus_sr.no_urut_ruta = sr.no_urut_ruta
                      AND sus_p.tahun = p.tahun
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

        $count = 0;
        $this->pdo->beginTransaction();
        try {
            $sql = "UPDATE sampel_ruta SET `{$field}` = :val WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            foreach ($targetIds as $id) {
                $stmt->execute([':val' => $value, ':id' => $id]);
                $count += $stmt->rowCount();
            }

            $userId = (int) ($currentUser['id'] ?? 0);
            $this->auditRepo->log(
                $userId > 0 ? $userId : null,
                'UPDATE',
                'sampel_ruta',
                'batch_transfer_seruti',
                null,
                ['field' => $field, 'value' => $value, 'nks' => $nks, 'affected' => $count],
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

    private function buildScopeJoin(int $pengolahId): array
    {
        if ($pengolahId > 0) {
            return [
                'join' => 'JOIN penugasan pg ON pg.sampel_id = sp.id AND pg.pengolah_id = :pengolah_id',
                'params' => [':pengolah_id' => $pengolahId],
            ];
        }
        return ['join' => '', 'params' => []];
    }
}
