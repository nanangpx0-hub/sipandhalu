<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class PeriodeRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<int,array<string,mixed>> */
    public function all(): array
    {
        return $this->pdo->query('SELECT * FROM periode ORDER BY tahun DESC, jenis')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM periode WHERE id=:id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch();
        return $r === false ? null : $r;
    }

    public function findByTahunJenis(int $tahun, string $jenis): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM periode WHERE tahun=:t AND jenis=:j LIMIT 1');
        $stmt->execute([':t' => $tahun, ':j' => $jenis]);
        $r = $stmt->fetch();
        return $r === false ? null : $r;
    }

    public function create(array $d): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO periode (tahun, jenis, label, tgl_mulai, tgl_selesai, status, catatan)
             VALUES (:t,:j,:l,:m,:s,:st,:c)'
        );
        $stmt->execute([
            ':t' => $d['tahun'], ':j' => $d['jenis'], ':l' => $d['label'],
            ':m' => $d['tgl_mulai'] ?: null, ':s' => $d['tgl_selesai'] ?: null,
            ':st' => $d['status'] ?? 'DRAFT', ':c' => $d['catatan'] ?: null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function setStatus(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE periode SET status=:s WHERE id=:id');
        $stmt->execute([':s' => $status, ':id' => $id]);
    }

    /** Ringkasan 1 periode: n SLS, target, muatan, progres dokumen (CTE). */
    public function ringkasan(int $periodeId): array
    {
        $sql = <<<'SQL'
            WITH s AS (
              SELECT COUNT(*) AS n_sls,
                COALESCE(SUM(target_sampel),0) AS target,
                COALESCE(SUM(muatan_awal),0) AS muatan,
                COALESCE(SUM(hasil_kk),0) AS kk,
                COALESCE(SUM(hasil_rt),0) AS rt,
                COALESCE(SUM(dokumen_vsen=1),0) AS dok,
                COALESCE(SUM(peta_ws=1),0) AS peta
              FROM sampel WHERE periode_id=:p
            )
            SELECT * FROM s
            SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':p' => $periodeId]);
        return $stmt->fetch() ?: [];
    }

    /**
     * Beban per pengolah dalam 1 periode + ranking (window).
     * @return array<int,array<string,mixed>>
     */
    public function bebanPengolah(int $periodeId): array
    {
        $sql = <<<'SQL'
            WITH b AS (
              SELECT o.nama AS pengolah, COUNT(*) AS n_sls,
                COALESCE(SUM(sp.target_sampel),0) AS target
              FROM penugasan pg
              JOIN sampel sp ON sp.id=pg.sampel_id
              JOIN orang o ON o.id=pg.pengolah_id
              WHERE sp.periode_id=:p AND pg.status IN ('AKTIF','SELESAI')
              GROUP BY o.nama
            )
            SELECT pengolah, n_sls, target,
              RANK() OVER (ORDER BY n_sls DESC) AS peringkat,
              SUM(n_sls) OVER () AS total_sls
            FROM b ORDER BY peringkat, pengolah
            SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':p' => $periodeId]);
        return $stmt->fetchAll();
    }
}
