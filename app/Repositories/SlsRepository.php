<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class SlsRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array{data:array<int,array<string,mixed>>,total:int} */
    public function paginate(string $q, int $page, int $perPage, string $kec = 'all'): array
    {
        $w = [];
        $p = [];
        if ($q !== '') {
            $w[] = '(s.nks LIKE :q1 OR s.kode_full LIKE :q2 OR s.nama_sls LIKE :q3 OR s.dusun LIKE :q4)';
            $p[':q1'] = '%' . $q . '%';
            $p[':q2'] = '%' . $q . '%';
            $p[':q3'] = '%' . $q . '%';
            $p[':q4'] = '%' . $q . '%';
        }
        if ($kec !== 'all') {
            $w[] = 's.kec=:k';
            $p[':k'] = $kec;
        }
        $where = $w === [] ? '' : 'WHERE ' . implode(' AND ', $w);
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM sls s {$where}");
        $stmt->execute($p);
        $total = (int) $stmt->fetchColumn();
        $off = max(0, ($page - 1) * $perPage);
        $stmt = $this->pdo->prepare(
            "SELECT s.*, k.nama AS nama_kec, d.nama AS nama_desa FROM sls s
             LEFT JOIN kecamatan k ON k.kode=s.kec LEFT JOIN desa d ON d.id=s.desa_id
             {$where} ORDER BY s.kec, s.desa, s.nks LIMIT :l OFFSET :o"
        );
        foreach ($p as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':l', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':o', $off, PDO::PARAM_INT);
        $stmt->execute();
        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    /** Semua baris untuk ekspor (dengan filter q/kec, tanpa LIMIT). */
    public function exportAll(string $q, string $kec = 'all'): array
    {
        $w = [];
        $p = [];
        if ($q !== '') {
            $w[] = '(s.nks LIKE :q1 OR s.kode_full LIKE :q2 OR s.nama_sls LIKE :q3 OR s.dusun LIKE :q4)';
            $p[':q1'] = '%' . $q . '%';
            $p[':q2'] = '%' . $q . '%';
            $p[':q3'] = '%' . $q . '%';
            $p[':q4'] = '%' . $q . '%';
        }
        if ($kec !== 'all') {
            $w[] = 's.kec=:k';
            $p[':k'] = $kec;
        }
        $where = $w === [] ? '' : 'WHERE ' . implode(' AND ', $w);
        $stmt = $this->pdo->prepare(
            "SELECT s.*, k.nama AS nama_kec, d.nama AS nama_desa FROM sls s
             LEFT JOIN kecamatan k ON k.kode=s.kec LEFT JOIN desa d ON d.id=s.desa_id
             {$where} ORDER BY s.kec, s.desa, s.nks"
        );
        $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM sls WHERE id=:id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch();
        return $r === false ? null : $r;
    }

    public function findByNks(string $nks): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM sls WHERE nks=:n LIMIT 1');
        $stmt->execute([':n' => $nks]);
        $r = $stmt->fetch();
        return $r === false ? null : $r;
    }

    public function findByFull(string $full): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM sls WHERE kode_full=:f LIMIT 1');
        $stmt->execute([':f' => $full]);
        $r = $stmt->fetch();
        return $r === false ? null : $r;
    }

    public function create(array $d): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO sls (kode_full, prov, kab, kec, desa, sls, sub, nks, desa_id,
              dusun, rw, rt, nama_sls, ketua, klasifikasi, jml_kk, jml_rt, is_aktif)
             VALUES (:f,:p,:kb,:kc,:ds,:sl,:sb,:n,:di,:du,:rw,:rt,:nm,:kt,:kl,:kk,:jr,:a)'
        );
        $stmt->execute([
            ':f' => $d['kode_full'] ?: null, ':p' => $d['prov'], ':kb' => $d['kab'],
            ':kc' => $d['kec'], ':ds' => $d['desa'], ':sl' => $d['sls'] ?: null,
            ':sb' => $d['sub'] ?: null, ':n' => $d['nks'], ':di' => $d['desa_id'] ?: null,
            ':du' => $d['dusun'] ?: null, ':rw' => $d['rw'] ?: null, ':rt' => $d['rt'] ?: null,
            ':nm' => $d['nama_sls'] ?: null, ':kt' => $d['ketua'] ?: null,
            ':kl' => $d['klasifikasi'] ?: null, ':kk' => $d['jml_kk'] ?: null,
            ':jr' => $d['jml_rt'] ?: null, ':a' => $d['is_aktif'] ?? 1,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $d): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE sls SET kode_full=:f, kec=:kc, desa=:ds, sls=:sl, sub=:sb, nks=:n,
              desa_id=:di, dusun=:du, rw=:rw, rt=:rt, nama_sls=:nm, ketua=:kt,
              klasifikasi=:kl, jml_kk=:kk, jml_rt=:jr, is_aktif=:a WHERE id=:id'
        );
        $stmt->execute([
            ':f' => $d['kode_full'] ?: null, ':kc' => $d['kec'], ':ds' => $d['desa'],
            ':sl' => $d['sls'] ?: null, ':sb' => $d['sub'] ?: null, ':n' => $d['nks'],
            ':di' => $d['desa_id'] ?: null, ':du' => $d['dusun'] ?: null,
            ':rw' => $d['rw'] ?: null, ':rt' => $d['rt'] ?: null, ':nm' => $d['nama_sls'] ?: null,
            ':kt' => $d['ketua'] ?: null, ':kl' => $d['klasifikasi'] ?: null,
            ':kk' => $d['jml_kk'] ?: null, ':jr' => $d['jml_rt'] ?: null,
            ':a' => $d['is_aktif'] ?? 1, ':id' => $id,
        ]);
    }
}
