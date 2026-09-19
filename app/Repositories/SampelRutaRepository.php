<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class SampelRutaRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** Semua baris ruta (1..10) milik satu sampel, urut no_urut_ruta. */
    public function getBySampelId(int $sampelId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM sampel_ruta WHERE sampel_id = :s ORDER BY no_urut_ruta ASC'
        );
        $stmt->execute([':s' => $sampelId]);
        return $stmt->fetchAll();
    }

    /** Banyak baris ruta milik satu sampel. */
    public function countBySampelId(int $sampelId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM sampel_ruta WHERE sampel_id = :s');
        $stmt->execute([':s' => $sampelId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Rekap progres per sampel untuk satu periode, key = sampel_id.
     * @return array<int,array{sampel_id:int,selesai:int,modul:int,kp:int}>
     */
    public function getSummaryByPeriodeId(int $periodeId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT sr.sampel_id,
                    SUM(CASE WHEN sr.status_selesai='SUDAH' THEN 1 ELSE 0 END) AS selesai,
                    SUM(CASE WHEN sr.catatan_modul=1 THEN 1 ELSE 0 END) AS modul,
                    SUM(CASE WHEN sr.catatan_kp=1 THEN 1 ELSE 0 END) AS kp
             FROM sampel_ruta sr
             JOIN sampel sp ON sp.id = sr.sampel_id
             WHERE sp.periode_id = :p
             GROUP BY sr.sampel_id"
        );
        $stmt->execute([':p' => $periodeId]);
        $out = [];
        foreach ($stmt->fetchAll() as $r) {
            $out[(int) $r['sampel_id']] = [
                'sampel_id' => (int) $r['sampel_id'],
                'selesai' => (int) $r['selesai'],
                'modul' => (int) $r['modul'],
                'kp' => (int) $r['kp'],
            ];
        }
        return $out;
    }

    /** Upsert satu baris ruta. $data key: status_selesai, catatan_modul, catatan_kp, tgl_pengiriman, ttd_sos, ttd_ipds. */
    public function upsertRuta(int $sampelId, int $noUrutRuta, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO sampel_ruta (sampel_id, no_urut_ruta, status_selesai, catatan_modul, catatan_kp, tgl_pengiriman, ttd_sos, ttd_ipds)
             VALUES (:s, :n, :st, :m, :kp, :tgl, :tsos, :tipds)
             ON DUPLICATE KEY UPDATE
               status_selesai = VALUES(status_selesai),
               catatan_modul = VALUES(catatan_modul),
               catatan_kp = VALUES(catatan_kp),
               tgl_pengiriman = VALUES(tgl_pengiriman),
               ttd_sos = VALUES(ttd_sos),
               ttd_ipds = VALUES(ttd_ipds)'
        );
        $stmt->execute([
            ':s' => $sampelId,
            ':n' => $noUrutRuta,
            ':st' => in_array($data['status_selesai'] ?? null, ['SUDAH', 'BELUM'], true) ? $data['status_selesai'] : 'BELUM',
            ':m' => !empty($data['catatan_modul']) ? 1 : 0,
            ':kp' => !empty($data['catatan_kp']) ? 1 : 0,
            ':tgl' => !empty($data['tgl_pengiriman']) ? $data['tgl_pengiriman'] : null,
            ':tsos' => ($data['ttd_sos'] ?? '') !== '' ? mb_substr(trim((string) $data['ttd_sos']), 0, 100) : null,
            ':tipds' => ($data['ttd_ipds'] ?? '') !== '' ? mb_substr(trim((string) $data['ttd_ipds']), 0, 100) : null,
        ]);
        return true;
    }

    /**
     * Upsert banyak baris sekaligus. @return int jumlah baris yang diupsert.
     * @param array<int,array{sampel_id:int,no_urut_ruta:int,data:array<string,mixed>}> $rows
     */
    public function batchUpsert(array $rows): int
    {
        if ($rows === []) {
            return 0;
        }
        $this->pdo->beginTransaction();
        try {
            $n = 0;
            foreach ($rows as $r) {
                $this->upsertRuta((int) $r['sampel_id'], (int) $r['no_urut_ruta'], (array) $r['data']);
                $n++;
            }
            $this->pdo->commit();
            return $n;
        } catch (\Throwable $t) {
            $this->pdo->rollBack();
            throw $t;
        }
    }

    /** Tandai seluruh 10 ruta satu sampel selesai pada tanggal tertentu. @return int baris terpengaruh */
    public function markAllDone(int $sampelId, string $tglPengiriman, ?string $ttd): int
    {
        $stmt = $this->pdo->prepare(
            "UPDATE sampel_ruta
             SET status_selesai = 'SUDAH',
                 tgl_pengiriman = :tgl,
                 ttd_sos = COALESCE(:tsos, ttd_sos),
                 ttd_ipds = COALESCE(:tipds, ttd_ipds)
             WHERE sampel_id = :s"
        );
        $stmt->execute([
            ':tgl' => $tglPengiriman,
            ':tsos' => $ttd,
            ':tipds' => $ttd,
            ':s' => $sampelId,
        ]);
        return $stmt->rowCount();
    }

    /** Pastikan sampel memiliki 10 baris ruta default (idempoten). @return int baris baru dibuat */
    public function ensureDefaults(int $sampelId): int
    {
        if ($this->countBySampelId($sampelId) >= 10) {
            return 0;
        }
        $stmt = $this->pdo->prepare(
            'INSERT IGNORE INTO sampel_ruta (sampel_id, no_urut_ruta)
             SELECT :s, n.no
             FROM (SELECT 1 AS no UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
                   UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10) n'
        );
        $stmt->execute([':s' => $sampelId]);
        return $stmt->rowCount();
    }

    /** Ambil 1 baris ruta lengkap dengan informasi SLS, wilayah, dan penugasan. */
    public function findRutaById(int $rutaId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT sr.*, sp.periode_id, sl.nks, sl.nama_sls, d.nama AS nama_desa, k.nama AS nama_kecamatan,
                    pg.pcl_id, o_pcl.nama AS nama_pcl, o_pcl.no_hp AS hp_pcl,
                    pg.pml_id, o_pml.nama AS nama_pml, o_pml.no_hp AS hp_pml,
                    pg.pengolah_id, o_peng.nama AS nama_pengolah, o_peng.no_hp AS hp_pengolah
             FROM sampel_ruta sr
             JOIN sampel sp ON sp.id = sr.sampel_id
             JOIN sls sl ON sl.id = sp.sls_id
             LEFT JOIN desa d ON d.id = sl.desa_id
             LEFT JOIN kecamatan k ON k.kode = d.kecamatan_kode
             LEFT JOIN penugasan pg ON pg.sampel_id = sp.id
             LEFT JOIN orang o_pcl ON o_pcl.id = pg.pcl_id
             LEFT JOIN orang o_pml ON o_pml.id = pg.pml_id
             LEFT JOIN orang o_peng ON o_peng.id = pg.pengolah_id
             WHERE sr.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $rutaId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Ambil daftar ruta untuk Lembar Kerja Pengolahan berdasarkan periode dan filter.
     * @param array{pengolah_id?:int,pcl_id?:int,pml_id?:int,lapangan_id?:int,status_dokumen?:string,has_error?:string|int,q?:string} $filters
     */
    public function getPengolahanRuta(int $periodeId, array $filters = []): array
    {
        $sql = 'SELECT sr.*, sp.periode_id, sl.nks, sl.nama_sls, sl.kode_full,
                       d.nama AS nama_desa, k.nama AS nama_kecamatan,
                       pg.pcl_id, o_pcl.nama AS nama_pcl, o_pcl.no_hp AS hp_pcl,
                       pg.pml_id, o_pml.nama AS nama_pml, o_pml.no_hp AS hp_pml,
                       pg.pengolah_id, o_peng.nama AS nama_pengolah, o_peng.no_hp AS hp_pengolah
                FROM sampel_ruta sr
                JOIN sampel sp ON sp.id = sr.sampel_id
                JOIN sls sl ON sl.id = sp.sls_id
                LEFT JOIN desa d ON d.id = sl.desa_id
                LEFT JOIN kecamatan k ON k.kode = d.kecamatan_kode
                LEFT JOIN penugasan pg ON pg.sampel_id = sp.id
                LEFT JOIN orang o_pcl ON o_pcl.id = pg.pcl_id
                LEFT JOIN orang o_pml ON o_pml.id = pg.pml_id
                LEFT JOIN orang o_peng ON o_peng.id = pg.pengolah_id
                WHERE sp.periode_id = :p';
        $params = [':p' => $periodeId];

        if (!empty($filters['pengolah_id'])) {
            $sql .= ' AND pg.pengolah_id = :pengolah_id';
            $params[':pengolah_id'] = (int) $filters['pengolah_id'];
        }
        if (!empty($filters['pcl_id'])) {
            $sql .= ' AND pg.pcl_id = :pcl_id';
            $params[':pcl_id'] = (int) $filters['pcl_id'];
        }
        if (!empty($filters['pml_id'])) {
            $sql .= ' AND pg.pml_id = :pml_id';
            $params[':pml_id'] = (int) $filters['pml_id'];
        }
        if (!empty($filters['lapangan_id'])) {
            $sql .= ' AND (pg.pcl_id = :lapangan_id OR pg.pml_id = :lapangan_id2)';
            $params[':lapangan_id'] = (int) $filters['lapangan_id'];
            $params[':lapangan_id2'] = (int) $filters['lapangan_id'];
        }
        if (!empty($filters['status_dokumen'])) {
            $sql .= ' AND sr.status_dokumen = :status_dokumen';
            $params[':status_dokumen'] = $filters['status_dokumen'];
        }
        if (isset($filters['has_error']) && $filters['has_error'] !== '') {
            if ((string) $filters['has_error'] === '1') {
                $sql .= ' AND (sr.ket_kp_pengolah IS NOT NULL OR sr.ket_m_pengolah IS NOT NULL)';
            } elseif ((string) $filters['has_error'] === '0') {
                $sql .= ' AND sr.ket_kp_pengolah IS NULL AND sr.ket_m_pengolah IS NULL';
            }
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
        return $stmt->fetchAll();
    }

    /** Ringkasan progres pengolahan sampel tingkat periode. */
    public function getPengolahanSummary(int $periodeId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT 
                COUNT(*) AS total_ruta,
                SUM(CASE WHEN sr.status_dokumen='ADA' THEN 1 ELSE 0 END) AS dok_ada,
                SUM(CASE WHEN sr.status_dokumen='BELUM' THEN 1 ELSE 0 END) AS dok_belum,
                SUM(sr.status_transfer_k) AS transfer_k,
                SUM(sr.status_transfer_kp) AS transfer_kp,
                SUM(sr.status_transfer_seruti) AS transfer_seruti,
                SUM(CASE WHEN sr.ket_kp_pengolah IS NOT NULL OR sr.ket_m_pengolah IS NOT NULL THEN 1 ELSE 0 END) AS ada_catatan_error,
                SUM(CASE WHEN sr.status_selesai='SUDAH' THEN 1 ELSE 0 END) AS selesai
             FROM sampel_ruta sr
             JOIN sampel sp ON sp.id = sr.sampel_id
             WHERE sp.periode_id = :p"
        );
        $stmt->execute([':p' => $periodeId]);
        $summary = $stmt->fetch() ?: [];

        // Rekap per pengolah
        $stmtBeban = $this->pdo->prepare(
            "SELECT o.id AS pengolah_id, o.nama AS pengolah_nama,
                    COUNT(DISTINCT sp.id) AS n_sls,
                    COUNT(sr.id) AS total_ruta,
                    SUM(CASE WHEN sr.status_dokumen='ADA' THEN 1 ELSE 0 END) AS dok_ada,
                    SUM(sr.status_transfer_k) AS transfer_k,
                    SUM(sr.status_transfer_kp) AS transfer_kp,
                    SUM(CASE WHEN sr.ket_kp_pengolah IS NOT NULL OR sr.ket_m_pengolah IS NOT NULL THEN 1 ELSE 0 END) AS error_count
             FROM penugasan pg
             JOIN sampel sp ON sp.id = pg.sampel_id
             JOIN orang o ON o.id = pg.pengolah_id
             JOIN sampel_ruta sr ON sr.sampel_id = sp.id
             WHERE sp.periode_id = :p
             GROUP BY o.id, o.nama
             ORDER BY o.nama ASC"
        );
        $stmtBeban->execute([':p' => $periodeId]);
        $summary['beban_pengolah'] = $stmtBeban->fetchAll();

        return $summary;
    }

    /** Update kolom pengolahan satu baris ruta. */
    public function updatePengolahanRow(int $rutaId, array $data): bool
    {
        $allowedFields = [
            'status_dokumen', 'status_transfer_k', 'status_transfer_kp', 'status_transfer_seruti',
            'status_selesai', 'catatan_kp', 'ket_kp_pengolah', 'ket_kp_lapangan', 'ket_kp_sosial',
            'catatan_modul', 'ket_m_pengolah', 'ket_m_lapangan', 'ket_m_sosial', 'uji_petik_pengawas',
            'tgl_pengiriman', 'ttd_sos', 'ttd_ipds'
        ];

        $sets = [];
        $params = [':id' => $rutaId];

        foreach ($allowedFields as $f) {
            if (array_key_exists($f, $data)) {
                $val = $data[$f];
                if (in_array($f, ['status_transfer_k', 'status_transfer_kp', 'status_transfer_seruti', 'catatan_kp', 'catatan_modul'], true)) {
                    $val = !empty($val) ? 1 : 0;
                } elseif (in_array($f, ['ket_kp_pengolah', 'ket_kp_lapangan', 'ket_kp_sosial', 'ket_m_pengolah', 'ket_m_lapangan', 'ket_m_sosial', 'uji_petik_pengawas'], true)) {
                    $val = ($val !== null && trim((string)$val) !== '') ? trim((string)$val) : null;
                }
                $sets[] = "`{$f}` = :{$f}";
                $params[":{$f}"] = $val;
            }
        }

        // Otomatis sinkronisasi flag catatan jika keterangan terisi
        if (array_key_exists('ket_kp_pengolah', $data) && !array_key_exists('catatan_kp', $data)) {
            $hasKp = ($data['ket_kp_pengolah'] !== null && trim((string)$data['ket_kp_pengolah']) !== '');
            $sets[] = '`catatan_kp` = :auto_ckp';
            $params[':auto_ckp'] = $hasKp ? 1 : 0;
        }
        if (array_key_exists('ket_m_pengolah', $data) && !array_key_exists('catatan_modul', $data)) {
            $hasM = ($data['ket_m_pengolah'] !== null && trim((string)$data['ket_m_pengolah']) !== '');
            $sets[] = '`catatan_modul` = :auto_cmod';
            $params[':auto_cmod'] = $hasM ? 1 : 0;
        }

        if ($sets === []) {
            return false;
        }

        $sql = 'UPDATE sampel_ruta SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}

