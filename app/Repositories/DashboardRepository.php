<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository agregasi Dashboard Monitoring (read-only).
 *
 * Prinsip "Zero Data Discrepancy":
 * SEMUA query (KPI, tren, beban petugas, komposisi, grid) wajib memakai
 * buildWhere() + baseFrom() yang sama. Dengan begitu definisi filter tidak
 * mungkin berbeda antar-widget, sehingga angka KPI == jumlah chart == total grid.
 *
 * Semua nilai masuk lewat PDO prepared statement + whitelist kolom sort.
 */
final class DashboardRepository
{
    /** Whitelist kolom pengurutan grid — mencegah SQL injection via ?sort= */
    public const SORTABLE = [
        'nks' => 'sl.nks',
        'no_urut_ruta' => 'sr.no_urut_ruta',
        'kecamatan' => 'k.nama',
        'desa' => 'd.nama',
        'pcl' => 'o_pcl.nama',
        'pml' => 'o_pml.nama',
        'pengolah' => 'o_peng.nama',
        'status_dokumen' => 'sr.status_dokumen',
        'status_selesai' => 'sr.status_selesai',
        'tgl_pengiriman' => 'sr.tgl_pengiriman',
        'updated_at' => 'sr.updated_at',
    ];

    private const MAX_PER_PAGE = 100;

    public function __construct(private PDO $pdo)
    {
    }

    // ------------------------------------------------------------------ inti

    /**
     * Definisi filter tunggal untuk seluruh dashboard.
     *
     * @param array<string,mixed> $f
     * @return array{0:string,1:array<string,mixed>} [sqlWhere, bindParams]
     */
    private function buildWhere(array $f): array
    {
        $w = ['sp.periode_id = :periode_id'];
        $p = [':periode_id' => (int) ($f['periode_id'] ?? 0)];

        if (($f['kec'] ?? '') !== '') {
            $w[] = 'sl.kec = :kec';
            $p[':kec'] = (string) $f['kec'];
        }
        if (!empty($f['desa_id'])) {
            $w[] = 'sl.desa_id = :desa_id';
            $p[':desa_id'] = (int) $f['desa_id'];
        }
        if (!empty($f['pengolah_id'])) {
            $w[] = 'pg.pengolah_id = :pengolah_id';
            $p[':pengolah_id'] = (int) $f['pengolah_id'];
        }
        if (!empty($f['pcl_id'])) {
            $w[] = 'pg.pcl_id = :pcl_id';
            $p[':pcl_id'] = (int) $f['pcl_id'];
        }
        if (!empty($f['pml_id'])) {
            $w[] = 'pg.pml_id = :pml_id';
            $p[':pml_id'] = (int) $f['pml_id'];
        }
        if (($f['status_dokumen'] ?? '') !== '') {
            $w[] = 'sr.status_dokumen = :status_dokumen';
            $p[':status_dokumen'] = (string) $f['status_dokumen'];
        }
        if (($f['status_selesai'] ?? '') !== '') {
            $w[] = 'sr.status_selesai = :status_selesai';
            $p[':status_selesai'] = (string) $f['status_selesai'];
        }

        // Kendala/anomali: catatan error pengolah atau temuan uji petik pengawas.
        if (isset($f['has_error']) && (string) $f['has_error'] !== '') {
            if ((string) $f['has_error'] === '1') {
                $w[] = '(' . $this->anomaliExpr() . ')';
            } else {
                $w[] = '(sr.ket_kp_pengolah IS NULL AND sr.ket_m_pengolah IS NULL AND sr.uji_petik_pengawas IS NULL)';
            }
        }

        // Tahap funnel: dokumen -> entri (transfer K) -> valid (transfer KP) -> Seruti.
        $stage = (string) ($f['transfer_stage'] ?? '');
        if ($stage === 'K') {
            $w[] = 'sr.status_transfer_k = 1';
        } elseif ($stage === 'KP') {
            $w[] = 'sr.status_transfer_kp = 1';
        } elseif ($stage === 'SERUTI') {
            $w[] = 'sr.status_transfer_seruti = 1';
        } elseif ($stage === 'BELUM_K') {
            $w[] = "sr.status_dokumen = 'ADA' AND sr.status_transfer_k = 0";
        }

        // Rentang waktu berbasis aktivitas terakhir baris ruta (updated_at).
        if (($f['date_from'] ?? '') !== '') {
            $w[] = 'DATE(sr.updated_at) >= :date_from';
            $p[':date_from'] = (string) $f['date_from'];
        }
        if (($f['date_to'] ?? '') !== '') {
            $w[] = 'DATE(sr.updated_at) <= :date_to';
            $p[':date_to'] = (string) $f['date_to'];
        }

        if (($f['q'] ?? '') !== '') {
            $w[] = '(sl.nks LIKE :q_nks OR sl.nama_sls LIKE :q_sls OR d.nama LIKE :q_desa
                     OR k.nama LIKE :q_kec OR o_pcl.nama LIKE :q_pcl
                     OR o_pml.nama LIKE :q_pml OR o_peng.nama LIKE :q_peng)';
            $needle = '%' . (string) $f['q'] . '%';
            foreach (['nks', 'sls', 'desa', 'kec', 'pcl', 'pml', 'peng'] as $alias) {
                $p[':q_' . $alias] = $needle;
            }
        }

        return [implode(' AND ', $w), $p];
    }

    /** FROM + JOIN tunggal untuk semua agregasi dashboard. */
    private function baseFrom(): string
    {
        return 'FROM sampel_ruta sr
                JOIN sampel sp ON sp.id = sr.sampel_id
                JOIN sls sl ON sl.id = sp.sls_id
                LEFT JOIN desa d ON d.id = sl.desa_id
                LEFT JOIN kecamatan k ON k.kode = sl.kec
                LEFT JOIN penugasan pg ON pg.sampel_id = sp.id
                LEFT JOIN orang o_pcl ON o_pcl.id = pg.pcl_id
                LEFT JOIN orang o_pml ON o_pml.id = pg.pml_id
                LEFT JOIN orang o_peng ON o_peng.id = pg.pengolah_id';
    }

    /** Ekspresi SQL: baris punya catatan kendala / temuan. */
    private function anomaliExpr(): string
    {
        return '(sr.ket_kp_pengolah IS NOT NULL OR sr.ket_m_pengolah IS NOT NULL OR sr.uji_petik_pengawas IS NOT NULL)';
    }

    /**
     * Jalankan query agregat dengan WHERE bersama.
     *
     * @param array<string,mixed> $f
     * @return array<string,mixed>
     */
    private function fetchOne(string $select, array $f): array
    {
        [$where, $params] = $this->buildWhere($f);
        $stmt = $this->pdo->prepare($select . ' ' . $this->baseFrom() . ' WHERE ' . $where);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return $row === false ? [] : $row;
    }

    // --------------------------------------------------------------- agregat

    /**
     * Ringkasan satu baris: seluruh angka dasar dashboard.
     *
     * @param array<string,mixed> $f
     * @return array<string,mixed>
     */
    public function summary(array $f): array
    {
        $anomali = $this->anomaliExpr();

        $select = "SELECT
            COUNT(*) AS total_ruta,
            COUNT(DISTINCT sp.id) AS total_sls,
            COALESCE(SUM(sr.status_dokumen = 'ADA'), 0) AS dok_ada,
            COALESCE(SUM(sr.status_transfer_k), 0) AS transfer_k,
            COALESCE(SUM(sr.status_transfer_kp), 0) AS transfer_kp,
            COALESCE(SUM(sr.status_transfer_seruti), 0) AS transfer_seruti,
            COALESCE(SUM(sr.status_selesai = 'SUDAH'), 0) AS selesai,
            COALESCE(SUM({$anomali}), 0) AS anomali,
            COALESCE(SUM(sr.uji_petik_pengawas IS NOT NULL), 0) AS uji_petik,
            COALESCE(SUM(sr.status_dokumen = 'ADA' AND sr.status_transfer_k = 0), 0) AS pending_entri,
            COALESCE(SUM(sr.status_transfer_k = 1 AND sr.status_transfer_kp = 0), 0) AS pending_validasi,
            COALESCE(SUM(sr.status_dokumen = 'ADA' AND sr.status_transfer_k = 1), 0) AS dok_ada_k,
            COALESCE(SUM(sr.status_transfer_k = 1 AND sr.status_transfer_kp = 1), 0) AS k_kp,
            COALESCE(SUM(sr.status_transfer_kp = 1 AND sr.status_transfer_k = 0), 0) AS kp_tanpa_k,
            COALESCE(SUM(sr.updated_at IS NULL), 0) AS tanpa_tanggal,
            MAX(sr.updated_at) AS terakhir_aktivitas";

        return $this->fetchOne($select, $f);
    }

    /**
     * Target ruta milik SLS yang lolos filter (target_sampel per SLS).
     *
     * @param array<string,mixed> $f
     */
    public function targetOfFiltered(array $f): int
    {
        [$where, $params] = $this->buildWhere($f);
        $sql = 'SELECT COALESCE(SUM(t.target), 0) FROM (
                  SELECT sp.id AS sid, sp.target_sampel AS target
                  ' . $this->baseFrom() . '
                  WHERE ' . $where . '
                  GROUP BY sp.id, sp.target_sampel
                ) t';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Seri harian (bukan kumulatif) berbasis tanggal aktivitas terakhir baris ruta.
     *
     * @param array<string,mixed> $f
     * @return array<int,array<string,mixed>>
     */
    public function trendDaily(array $f): array
    {
        [$where, $params] = $this->buildWhere($f);
        $anomali = $this->anomaliExpr();

        $sql = "SELECT DATE(sr.updated_at) AS tanggal,
                       COALESCE(SUM(sr.status_dokumen = 'ADA'), 0) AS dok_ada,
                       COALESCE(SUM(sr.status_transfer_k), 0) AS transfer_k,
                       COALESCE(SUM(sr.status_transfer_kp), 0) AS transfer_kp,
                       COALESCE(SUM({$anomali}), 0) AS anomali,
                       COALESCE(SUM(sr.status_selesai = 'SUDAH'), 0) AS selesai,
                       COALESCE(SUM(sr.status_dokumen = 'ADA' AND sr.status_transfer_k = 0), 0) AS pending_entri,
                       COALESCE(SUM(sr.status_transfer_k = 1 AND sr.status_transfer_kp = 0), 0) AS pending_validasi,
                       COUNT(*) AS total
                " . $this->baseFrom() . '
                WHERE ' . $where . ' AND sr.updated_at IS NOT NULL
                GROUP BY DATE(sr.updated_at)
                ORDER BY tanggal ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Beban & capaian per pengolah (untuk horizontal bar ranking).
     *
     * @param array<string,mixed> $f
     * @return array<int,array<string,mixed>>
     */
    public function bebanByPengolah(array $f): array
    {
        [$where, $params] = $this->buildWhere($f);
        $anomali = $this->anomaliExpr();

        $sql = "SELECT o_peng.id AS orang_id, o_peng.nama AS nama,
                       COUNT(DISTINCT sp.id) AS total_sls,
                       COUNT(*) AS total_ruta,
                       COALESCE(SUM(sr.status_dokumen = 'ADA'), 0) AS dok_ada,
                       COALESCE(SUM(sr.status_transfer_k), 0) AS transfer_k,
                       COALESCE(SUM(sr.status_transfer_kp), 0) AS transfer_kp,
                       COALESCE(SUM({$anomali}), 0) AS anomali
                " . $this->baseFrom() . '
                WHERE ' . $where . ' AND o_peng.id IS NOT NULL
                GROUP BY o_peng.id, o_peng.nama
                ORDER BY total_ruta DESC, nama ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Baris ruta tanpa pengolah — dipakai memverifikasi
     * (jumlah beban semua pengolah + baris tanpa pengolah) == total ruta.
     *
     * @param array<string,mixed> $f
     */
    public function rowsWithoutPengolah(array $f): int
    {
        [$where, $params] = $this->buildWhere($f);
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) ' . $this->baseFrom() . ' WHERE ' . $where . ' AND o_peng.id IS NULL'
        );
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Komposisi funnel status — partisi eksklusif & menyeluruh.
     * Urutan prioritas CASE menjamin jumlah seluruh bucket == total ruta.
     *
     * @param array<string,mixed> $f
     * @return array<int,array<string,mixed>>
     */
    public function composition(array $f): array
    {
        [$where, $params] = $this->buildWhere($f);
        $anomali = $this->anomaliExpr();

        $sql = "SELECT bucket, COUNT(*) AS n, COALESCE(SUM(anomali), 0) AS anomali FROM (
                  SELECT CASE
                           WHEN sr.status_transfer_kp = 1 THEN 'TERVALIDASI'
                           WHEN sr.status_transfer_k = 1 THEN 'ENTRI'
                           WHEN sr.status_dokumen = 'ADA' THEN 'DOKUMEN'
                           ELSE 'BELUM'
                         END AS bucket,
                         ({$anomali}) AS anomali
                  " . $this->baseFrom() . '
                  WHERE ' . $where . '
                ) x
                GROUP BY bucket';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Rincian kendala per sumber catatan (dapat bertumpang di satu baris,
     * sehingga tidak dijumlahkan sebagai persentase).
     *
     * @param array<string,mixed> $f
     * @return array<string,mixed>
     */
    public function kendala(array $f): array
    {
        $select = 'SELECT
            COALESCE(SUM(sr.ket_kp_pengolah IS NOT NULL), 0) AS kp_pengolah,
            COALESCE(SUM(sr.ket_kp_lapangan IS NOT NULL), 0) AS kp_lapangan,
            COALESCE(SUM(sr.ket_kp_sosial IS NOT NULL), 0) AS kp_sosial,
            COALESCE(SUM(sr.ket_m_pengolah IS NOT NULL), 0) AS m_pengolah,
            COALESCE(SUM(sr.ket_m_lapangan IS NOT NULL), 0) AS m_lapangan,
            COALESCE(SUM(sr.ket_m_sosial IS NOT NULL), 0) AS m_sosial,
            COALESCE(SUM(sr.uji_petik_pengawas IS NOT NULL), 0) AS uji_petik';

        return $this->fetchOne($select, $f);
    }

    /**
     * Total baris grid (memakai WHERE yang sama dengan agregat).
     *
     * @param array<string,mixed> $f
     */
    public function gridCount(array $f): int
    {
        [$where, $params] = $this->buildWhere($f);
        $stmt = $this->pdo->prepare('SELECT COUNT(*) ' . $this->baseFrom() . ' WHERE ' . $where);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Data grid tingkat ruta dengan server-side sort + pagination.
     *
     * @param array<string,mixed> $f
     * @return array<int,array<string,mixed>>
     */
    public function gridRows(array $f, string $sortKey, string $dir, int $page, int $perPage): array
    {
        [$where, $params] = $this->buildWhere($f);

        $col = self::SORTABLE[$sortKey] ?? self::SORTABLE['nks'];
        $dirSql = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';

        $perPage = max(1, min(self::MAX_PER_PAGE, $perPage));
        $offset = max(0, (max(1, $page) - 1) * $perPage);

        $sql = "SELECT sr.id, sr.sampel_id, sr.no_urut_ruta, sr.status_dokumen, sr.status_selesai,
                       sr.status_transfer_k, sr.status_transfer_kp, sr.status_transfer_seruti,
                       sr.tgl_pengiriman, sr.updated_at, sr.ket_kp_pengolah, sr.ket_m_pengolah,
                       sr.uji_petik_pengawas, sr.ttd_sos, sr.ttd_ipds,
                       sp.periode_id, sp.target_sampel,
                       sl.nks, sl.kode_full, sl.nama_sls, sl.dusun, sl.rw, sl.rt, sl.kec,
                       d.nama AS nama_desa, k.nama AS nama_kecamatan,
                       pg.pcl_id, o_pcl.nama AS nama_pcl,
                       pg.pml_id, o_pml.nama AS nama_pml,
                       pg.pengolah_id, o_peng.nama AS nama_pengolah
                " . $this->baseFrom() . '
                WHERE ' . $where . "
                ORDER BY {$col} {$dirSql}, sl.nks ASC, sr.no_urut_ruta ASC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // ------------------------------------------------------------------ opsi

    /**
     * Opsi dropdown kecamatan yang punya sampel pada periode terpilih.
     *
     * @return array<int,array<string,mixed>>
     */
    public function optionsKecamatan(int $periodeId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT sl.kec AS kode, k.nama AS nama
             FROM sampel sp
             JOIN sls sl ON sl.id = sp.sls_id
             LEFT JOIN kecamatan k ON k.kode = sl.kec
             WHERE sp.periode_id = :p
             ORDER BY k.nama ASC, sl.kec ASC'
        );
        $stmt->execute([':p' => $periodeId]);

        return $stmt->fetchAll();
    }

    /**
     * Opsi dropdown desa (lengkap + kecamatan_kode untuk cascading client-side).
     *
     * @return array<int,array<string,mixed>>
     */
    public function optionsDesa(int $periodeId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT sl.desa_id AS id, d.nama AS nama, sl.kec AS kecamatan_kode
             FROM sampel sp
             JOIN sls sl ON sl.id = sp.sls_id
             JOIN desa d ON d.id = sl.desa_id
             WHERE sp.periode_id = :p
             ORDER BY d.nama ASC'
        );
        $stmt->execute([':p' => $periodeId]);

        return $stmt->fetchAll();
    }

    /**
     * Opsi dropdown petugas (PCL/PML/Pengolah) pada periode terpilih.
     *
     * @return array<int,array<string,mixed>>
     */
    public function optionsPetugas(int $periodeId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT o.id, o.nama, peran.peran, COUNT(*) AS jml
             FROM (
               SELECT sp.id AS sampel_id, pg.pcl_id AS orang_id, 'PCL' AS peran
                 FROM penugasan pg JOIN sampel sp ON sp.id = pg.sampel_id WHERE sp.periode_id = :p1
               UNION ALL
               SELECT sp.id, pg.pml_id, 'PML'
                 FROM penugasan pg JOIN sampel sp ON sp.id = pg.sampel_id WHERE sp.periode_id = :p2
               UNION ALL
               SELECT sp.id, pg.pengolah_id, 'PENGOLAH'
                 FROM penugasan pg JOIN sampel sp ON sp.id = pg.sampel_id WHERE sp.periode_id = :p3
             ) peran
             JOIN orang o ON o.id = peran.orang_id
             GROUP BY o.id, o.nama, peran.peran
             ORDER BY peran.peran ASC, o.nama ASC"
        );
        $stmt->execute([':p1' => $periodeId, ':p2' => $periodeId, ':p3' => $periodeId]);

        return $stmt->fetchAll();
    }

    /**
     * Waktu aktivitas terakhir — untuk indikator Live/Syncing/Offline.
     *
     * @return array<string,mixed>
     */
    public function activityStamp(int $periodeId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
               (SELECT MAX(sr.updated_at) FROM sampel_ruta sr
                 JOIN sampel sp ON sp.id = sr.sampel_id WHERE sp.periode_id = :p1) AS ruta_terakhir,
               (SELECT MAX(l.created_at) FROM audit_logs l) AS audit_terakhir,
               (SELECT COUNT(*) FROM audit_logs l
                 WHERE l.tabel_target IN ('sampel_ruta','sampel')
                   AND l.created_at >= (NOW() - INTERVAL 1 DAY)) AS audit_24jam"
        );
        $stmt->execute([':p1' => $periodeId]);
        $row = $stmt->fetch();

        return $row === false ? [] : $row;
    }

    // ---------------------------------------------------------------- detail

    /**
     * Detail satu baris ruta untuk drawer Quick View.
     *
     * @return array<string,mixed>|null
     */
    public function rutaDetail(int $rutaId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT sr.*, sp.periode_id, sp.target_sampel,
                    sl.nks, sl.kode_full, sl.nama_sls, sl.dusun, sl.rw, sl.rt,
                    sl.klasifikasi, sl.jml_kk,
                    d.nama AS nama_desa, k.nama AS nama_kecamatan,
                    pg.pcl_id, o_pcl.nama AS nama_pcl, o_pcl.no_hp AS hp_pcl,
                    pg.pml_id, o_pml.nama AS nama_pml, o_pml.no_hp AS hp_pml,
                    pg.pengolah_id, o_peng.nama AS nama_pengolah, o_peng.no_hp AS hp_pengolah
             FROM sampel_ruta sr
             JOIN sampel sp ON sp.id = sr.sampel_id
             JOIN sls sl ON sl.id = sp.sls_id
             LEFT JOIN desa d ON d.id = sl.desa_id
             LEFT JOIN kecamatan k ON k.kode = sl.kec
             LEFT JOIN penugasan pg ON pg.sampel_id = sp.id
             LEFT JOIN orang o_pcl ON o_pcl.id = pg.pcl_id
             LEFT JOIN orang o_pml ON o_pml.id = pg.pml_id
             LEFT JOIN orang o_peng ON o_peng.id = pg.pengolah_id
             WHERE sr.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $rutaId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Jejak audit satu baris ruta (auditability).
     *
     * @return array<int,array<string,mixed>>
     */
    public function rutaAuditTrail(int $rutaId, int $limit = 10): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT l.id, l.aksi, l.before_json, l.after_json, l.ip_address, l.created_at,
                    u.nama AS nama_user
             FROM audit_logs l
             LEFT JOIN users u ON u.id = l.user_id
             WHERE l.tabel_target = :t AND l.id_target = :i
             ORDER BY l.id DESC LIMIT :lim'
        );
        $stmt->bindValue(':t', 'sampel_ruta');
        $stmt->bindValue(':i', (string) $rutaId);
        $stmt->bindValue(':lim', max(1, min(50, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}