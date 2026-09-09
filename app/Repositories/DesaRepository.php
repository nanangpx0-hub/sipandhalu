<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class DesaRepository
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
            $w[] = '(d.nama LIKE :q1 OR d.kode LIKE :q2)';
            $p[':q1'] = '%' . $q . '%';
            $p[':q2'] = '%' . $q . '%';
        }
        if ($kec !== 'all') {
            $w[] = 'd.kecamatan_kode=:k';
            $p[':k'] = $kec;
        }
        $where = $w === [] ? '' : 'WHERE ' . implode(' AND ', $w);
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM desa d {$where}");
        $stmt->execute($p);
        $total = (int) $stmt->fetchColumn();
        $off = max(0, ($page - 1) * $perPage);
        $stmt = $this->pdo->prepare(
            "SELECT d.*, k.nama AS nama_kec FROM desa d
             LEFT JOIN kecamatan k ON k.kode=d.kecamatan_kode
             {$where} ORDER BY d.kecamatan_kode, d.kode LIMIT :l OFFSET :o"
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
        $stmt = $this->pdo->prepare('SELECT * FROM desa WHERE id=:id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch();
        return $r === false ? null : $r;
    }

    public function findByKode(string $kec, string $kode): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM desa WHERE kecamatan_kode=:k AND kode=:d LIMIT 1');
        $stmt->execute([':k' => $kec, ':d' => $kode]);
        $r = $stmt->fetch();
        return $r === false ? null : $r;
    }

    public function create(string $kec, string $kode, string $nama): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO desa (kecamatan_kode, kode, nama) VALUES (:k,:d,:n)');
        $stmt->execute([':k' => $kec, ':d' => $kode, ':n' => $nama]);
        return (int) $this->pdo->lastInsertId();
    }
}
