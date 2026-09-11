<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class OrangRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array{data:array<int,array<string,mixed>>,total:int} */
    public function paginate(string $q, int $page, int $perPage, string $status = 'all'): array
    {
        $where = [];
        $params = [];
        if ($q !== '') {
            $where[] = '(o.nama LIKE :q1 OR o.email LIKE :q2 OR o.no_hp LIKE :q3)';
            $params[':q1'] = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
            $params[':q3'] = '%' . $q . '%';
        }
        if ($status === 'aktif') {
            $where[] = 'o.is_aktif = 1';
        } elseif ($status === 'nonaktif') {
            $where[] = 'o.is_aktif = 0';
        }
        $w = $where === [] ? '' : 'WHERE ' . implode(' AND ', $where);

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM orang o {$w}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $offset = max(0, ($page - 1) * $perPage);
        // ranking beban per periode menyusul tahap 2 (butuh tabel penugasan)
        $stmt = $this->pdo->prepare(
            "SELECT o.*, (SELECT COUNT(*) FROM orang_alias a WHERE a.orang_id=o.id) AS jml_alias,
                    (SELECT COUNT(*) FROM users u WHERE u.orang_id=o.id) AS jml_akun
             FROM orang o {$w} ORDER BY o.nama ASC LIMIT :l OFFSET :o"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':l', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':o', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    /** Semua baris untuk ekspor (dengan filter q/status, tanpa LIMIT). */
    public function exportAll(string $q, string $status = 'all'): array
    {
        $where = [];
        $params = [];
        if ($q !== '') {
            $where[] = '(o.nama LIKE :q1 OR o.email LIKE :q2 OR o.no_hp LIKE :q3)';
            $params[':q1'] = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
            $params[':q3'] = '%' . $q . '%';
        }
        if ($status === 'aktif') {
            $where[] = 'o.is_aktif = 1';
        } elseif ($status === 'nonaktif') {
            $where[] = 'o.is_aktif = 0';
        }
        $w = $where === [] ? '' : 'WHERE ' . implode(' AND ', $where);
        $stmt = $this->pdo->prepare(
            "SELECT o.*, (SELECT COUNT(*) FROM orang_alias a WHERE a.orang_id=o.id) AS jml_alias
             FROM orang o {$w} ORDER BY o.nama ASC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM orang WHERE id=:id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function findByNormalized(string $normalized): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM orang WHERE nama_normalized=:n LIMIT 1');
        $stmt->execute([':n' => $normalized]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** Cari orang ATAU alias (untuk tahan typo Excel: Rozy/Rozi, Junaidi/...). */
    public function resolveAlias(string $namaBebas): ?array
    {
        $norm = mb_strtolower(trim((string) preg_replace('/\s+/', ' ', $namaBebas)), 'UTF-8');
        $stmt = $this->pdo->prepare('SELECT * FROM orang WHERE nama_normalized=:n LIMIT 1');
        $stmt->execute([':n' => $norm]);
        $row = $stmt->fetch();
        if ($row !== false) {
            return $row;
        }
        $stmt = $this->pdo->prepare(
            'SELECT o.* FROM orang_alias a JOIN orang o ON o.id=a.orang_id WHERE a.alias_normalized=:n LIMIT 1'
        );
        $stmt->execute([':n' => $norm]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function create(array $d): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO orang (nama, no_hp, email, alamat, is_aktif) VALUES (:nama,:hp,:email,:alamat,:aktif)'
        );
        $stmt->execute([
            ':nama' => $d['nama'],
            ':hp' => $d['no_hp'] ?: null,
            ':email' => $d['email'] ?: null,
            ':alamat' => $d['alamat'] ?: null,
            ':aktif' => $d['is_aktif'] ?? 1,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $d): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE orang SET nama=:nama, no_hp=:hp, email=:email, alamat=:alamat, is_aktif=:aktif WHERE id=:id'
        );
        $stmt->execute([
            ':nama' => $d['nama'],
            ':hp' => $d['no_hp'] ?: null,
            ':email' => $d['email'] ?: null,
            ':alamat' => $d['alamat'] ?: null,
            ':aktif' => $d['is_aktif'] ?? 1,
            ':id' => $id,
        ]);
    }

    public function setAktif(int $id, bool $aktif): void
    {
        $stmt = $this->pdo->prepare('UPDATE orang SET is_aktif=:a WHERE id=:id');
        $stmt->execute([':a' => $aktif ? 1 : 0, ':id' => $id]);
    }

    /** @return array<int,array<string,mixed>> */
    public function aliases(int $orangId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM orang_alias WHERE orang_id=:id ORDER BY alias_normalized');
        $stmt->execute([':id' => $orangId]);
        return $stmt->fetchAll();
    }

    public function addAlias(int $orangId, string $aliasNormalized): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO orang_alias (orang_id, alias_normalized) VALUES (:o,:a)');
        $stmt->execute([':o' => $orangId, ':a' => $aliasNormalized]);
    }

    public function deleteAlias(int $aliasId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM orang_alias WHERE id=:id');
        $stmt->execute([':id' => $aliasId]);
    }

    /** @return array<int,array<string,mixed>> untuk dropdown link akun */
    public function options(?string $q = null, int $limit = 50): array
    {
        if ($q === null || $q === '') {
            $stmt = $this->pdo->prepare('SELECT id, nama FROM orang WHERE is_aktif=1 ORDER BY nama LIMIT :l');
            $stmt->bindValue(':l', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $stmt = $this->pdo->prepare(
            'SELECT id, nama FROM orang WHERE is_aktif=1 AND nama LIKE :q ORDER BY nama LIMIT :l'
        );
        $stmt->bindValue(':q', '%' . $q . '%');
        $stmt->bindValue(':l', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
