<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class WilayahRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<int,array<string,mixed>> */
    public function allKecamatan(): array
    {
        return $this->pdo->query('SELECT * FROM kecamatan ORDER BY kode')->fetchAll();
    }

    public function findKecamatan(string $kode): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM kecamatan WHERE kode=:k LIMIT 1');
        $stmt->execute([':k' => $kode]);
        $r = $stmt->fetch();
        return $r === false ? null : $r;
    }

    public function createKecamatan(string $kode, string $nama): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO kecamatan (kode, nama) VALUES (:k,:n)');
        $stmt->execute([':k' => $kode, ':n' => $nama]);
    }
}
