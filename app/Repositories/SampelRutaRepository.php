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
}
