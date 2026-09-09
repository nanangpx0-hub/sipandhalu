<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class SampelRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array{data:array<int,array<string,mixed>>,total:int} */
    public function paginateByPeriode(int $periodeId, string $q, int $page, int $perPage): array
    {
        $w = 'sp.periode_id=:p';
        $p = [':p' => $periodeId];
        if ($q !== '') {
            $w .= ' AND (s.nks LIKE :q1 OR s.nama_sls LIKE :q2 OR s.kode_full LIKE :q3)';
            $p[':q1'] = '%' . $q . '%';
            $p[':q2'] = '%' . $q . '%';
            $p[':q3'] = '%' . $q . '%';
        }
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM sampel sp JOIN sls s ON s.id=sp.sls_id WHERE {$w}");
        $stmt->execute($p);
        $total = (int) $stmt->fetchColumn();
        $off = max(0, ($page - 1) * $perPage);
        $stmt = $this->pdo->prepare(
            "SELECT sp.*, s.nks, s.kode_full, s.nama_sls, s.kec, s.desa, s.dusun, s.rw, s.rt,
                    k.nama AS nama_kec, d.nama AS nama_desa,
                    o1.nama AS pcl, o2.nama AS pml, o3.nama AS pengolah
             FROM sampel sp JOIN sls s ON s.id=sp.sls_id
             LEFT JOIN kecamatan k ON k.kode=s.kec LEFT JOIN desa d ON d.id=s.desa_id
             LEFT JOIN penugasan pg ON pg.sampel_id=sp.id
             LEFT JOIN orang o1 ON o1.id=pg.pcl_id LEFT JOIN orang o2 ON o2.id=pg.pml_id
             LEFT JOIN orang o3 ON o3.id=pg.pengolah_id
             WHERE {$w} ORDER BY s.kec, s.desa, s.nks LIMIT :l OFFSET :o"
        );
        foreach ($p as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':l', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':o', $off, PDO::PARAM_INT);
        $stmt->execute();
        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    public function add(int $periodeId, int $slsId, int $target = 10, ?int $muatan = null): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO sampel (periode_id, sls_id, target_sampel, muatan_awal) VALUES (:p,:s,:t,:m)'
        );
        $stmt->execute([':p' => $periodeId, ':s' => $slsId, ':t' => $target, ':m' => $muatan]);
        return (int) $this->pdo->lastInsertId();
    }

    public function assign(int $sampelId, int $pcl, int $pml, int $pengolah): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO penugasan (sampel_id, pcl_id, pml_id, pengolah_id, status)
             VALUES (:s,:a,:b,:c,"AKTIF")
             ON DUPLICATE KEY UPDATE pcl_id=VALUES(pcl_id), pml_id=VALUES(pml_id),
               pengolah_id=VALUES(pengolah_id), status="AKTIF"'
        );
        $stmt->execute([':s' => $sampelId, ':a' => $pcl, ':b' => $pml, ':c' => $pengolah]);
    }

    public function setProgres(int $sampelId, ?int $kk, ?int $rt, bool $dok, bool $peta): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE sampel SET hasil_kk=:kk, hasil_rt=:rt, dokumen_vsen=:d, peta_ws=:p WHERE id=:id'
        );
        $stmt->execute([':kk' => $kk, ':rt' => $rt, ':d' => $dok ? 1 : 0, ':p' => $peta ? 1 : 0, ':id' => $sampelId]);
    }

    /**
     * Peran apa saja yang sudah dipegang 1 orang dalam 1 periode (validasi K4).
     * @return array<int,string> misal [12=>'PCL', 12=>'PML'] jika rangkap
     */
    public function peranOrangDiPeriode(int $periodeId, int $orangId): array
    {
        $sql = <<<'SQL'
            SELECT 'PCL' AS peran FROM penugasan pg JOIN sampel sp ON sp.id=pg.sampel_id
            WHERE sp.periode_id=:p AND pg.pcl_id=:o LIMIT 1
            SQL;
        $out = [];
        foreach (['PCL' => 'pcl_id', 'PML' => 'pml_id', 'PENGOLAH' => 'pengolah_id'] as $peran => $kol) {
            $stmt = $this->pdo->prepare(
                "SELECT 1 FROM penugasan pg JOIN sampel sp ON sp.id=pg.sampel_id
                 WHERE sp.periode_id=:p AND pg.{$kol}=:o LIMIT 1"
            );
            $stmt->execute([':p' => $periodeId, ':o' => $orangId]);
            if ($stmt->fetchColumn()) {
                $out[] = $peran;
            }
        }
        return $out;
    }
}
