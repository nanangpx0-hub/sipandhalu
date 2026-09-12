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
            "SELECT sp.*, s.nks, s.kode_full, s.nama_sls, s.kec, s.desa, s.sub, s.dusun, s.rw, s.rt,
                    k.nama AS nama_kec, d.nama AS nama_desa,
                    pg.pcl_id, pg.pml_id, pg.pengolah_id,
                    o1.nama AS pcl, o2.nama AS pml, o3.nama AS pengolah,
                    u1.nama AS nama_penerima_pemutakhiran,
                    op1.nama AS nama_penyerah_pemutakhiran,
                    u2.nama AS nama_penerima_peta,
                    op2.nama AS nama_penyerah_peta
             FROM sampel sp JOIN sls s ON s.id=sp.sls_id
             LEFT JOIN kecamatan k ON k.kode=s.kec LEFT JOIN desa d ON d.id=s.desa_id
             LEFT JOIN penugasan pg ON pg.sampel_id=sp.id
             LEFT JOIN orang o1 ON o1.id=pg.pcl_id LEFT JOIN orang o2 ON o2.id=pg.pml_id
             LEFT JOIN orang o3 ON o3.id=pg.pengolah_id
             LEFT JOIN users u1 ON u1.id=sp.dok_pemutakhiran_oleh
             LEFT JOIN orang op1 ON op1.id=sp.dok_pemutakhiran_penyerah
             LEFT JOIN users u2 ON u2.id=sp.peta_oleh
             LEFT JOIN orang op2 ON op2.id=sp.peta_penyerah
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

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT sp.*, s.nks, s.kode_full, s.nama_sls, s.kec, s.desa, s.sub, s.dusun, s.rw, s.rt,
                    k.nama AS nama_kec, d.nama AS nama_desa,
                    pg.pcl_id, pg.pml_id, pg.pengolah_id,
                    o1.nama AS pcl, o2.nama AS pml, o3.nama AS pengolah
             FROM sampel sp JOIN sls s ON s.id=sp.sls_id
             LEFT JOIN kecamatan k ON k.kode=s.kec LEFT JOIN desa d ON d.id=s.desa_id
             LEFT JOIN penugasan pg ON pg.sampel_id=sp.id
             LEFT JOIN orang o1 ON o1.id=pg.pcl_id LEFT JOIN orang o2 ON o2.id=pg.pml_id
             LEFT JOIN orang o3 ON o3.id=pg.pengolah_id
             WHERE sp.id = :id"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
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
     * Perbarui status fisik dokumen (penerimaan atau pengembalian).
     */
    public function updateDokumenFisik(int $sampelId, array $fields): void
    {
        $sets = [];
        $params = [':id' => $sampelId];
        foreach ($fields as $col => $val) {
            $paramKey = ':' . $col;
            $sets[] = "{$col} = {$paramKey}";
            $params[$paramKey] = $val;
        }
        if (empty($sets)) {
            return;
        }
        $sql = 'UPDATE sampel SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * Ambil daftar semua PML yang bertugas di suatu periode.
     * @return array<int,array<string,mixed>>
     */
    public function allPmlInPeriode(int $periodeId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.id, o.nama, COUNT(sp.id) AS jml_sls
             FROM penugasan pg
             JOIN sampel sp ON sp.id = pg.sampel_id
             JOIN orang o ON o.id = pg.pml_id
             WHERE sp.periode_id = :p
             GROUP BY o.id, o.nama
             ORDER BY o.nama ASC'
        );
        $stmt->execute([':p' => $periodeId]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil semua sampel di suatu periode yang dibina oleh PML tertentu.
     * @return array<int,array<string,mixed>>
     */
    public function findByPmlInPeriode(int $periodeId, int $pmlId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT sp.*, s.nks, s.kode_full, s.nama_sls, s.kec, s.desa, s.sub,
                    o1.nama AS pcl_nama, o1.id AS pcl_id
             FROM sampel sp
             JOIN sls s ON s.id = sp.sls_id
             JOIN penugasan pg ON pg.sampel_id = sp.id
             LEFT JOIN orang o1 ON o1.id = pg.pcl_id
             WHERE sp.periode_id = :p AND pg.pml_id = :pml
             ORDER BY s.kec, s.desa, s.nks ASC'
        );
        $stmt->execute([':p' => $periodeId, ':pml' => $pmlId]);
        return $stmt->fetchAll();
    }

    /**
     * Peran apa saja yang sudah dipegang 1 orang dalam 1 periode (validasi K4).
     * @return array<int,string> misal [12=>'PCL', 12=>'PML'] jika rangkap
     */
    public function peranOrangDiPeriode(int $periodeId, int $orangId): array
    {
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
