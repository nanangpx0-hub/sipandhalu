<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<int,array<string,mixed>> */
    public function roles(): array
    {
        return $this->pdo->query('SELECT * FROM roles ORDER BY id')->fetchAll();
    }

    /** @return array{data:array<int,array<string,mixed>>,total:int} */
    public function paginate(string $q, int $page, int $perPage, string $role = 'all', string $status = 'all'): array
    {
        $where = [];
        $params = [];
        if ($q !== '') {
            $where[] = '(u.nama LIKE :q1 OR u.email LIKE :q2 OR o.nama LIKE :q3)';
            $params[':q1'] = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
            $params[':q3'] = '%' . $q . '%';
        }
        if ($role !== 'all') {
            $where[] = 'r.code = :role';
            $params[':role'] = $role;
        }
        if ($status === 'aktif') {
            $where[] = 'u.is_aktif = 1';
        } elseif ($status === 'nonaktif') {
            $where[] = 'u.is_aktif = 0';
        }
        $w = $where === [] ? '' : 'WHERE ' . implode(' AND ', $where);

        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id LEFT JOIN orang o ON o.id=u.orang_id {$w}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $offset = max(0, ($page - 1) * $perPage);
        $stmt = $this->pdo->prepare(
            "SELECT u.*, r.code AS role_code, r.label AS role_label, o.nama AS nama_orang
             FROM users u JOIN roles r ON r.id=u.role_id LEFT JOIN orang o ON o.id=u.orang_id
             {$w} ORDER BY u.nama ASC LIMIT :l OFFSET :o"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':l', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':o', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.*, r.code AS role_code, r.label AS role_label FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=:id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.*, r.code AS role_code, r.label AS role_label FROM users u JOIN roles r ON r.id=u.role_id WHERE u.email=:e LIMIT 1'
        );
        $stmt->execute([':e' => mb_strtolower(trim($email), 'UTF-8')]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email=:e';
        $p = [':e' => mb_strtolower(trim($email), 'UTF-8')];
        if ($exceptId !== null) {
            $sql .= ' AND id<>:id';
            $p[':id'] = $exceptId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($p);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $d): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (orang_id, nama, email, password_hash, role_id, is_aktif, must_reset)
             VALUES (:o,:n,:e,:h,:r,:a,:m)'
        );
        $stmt->execute([
            ':o' => $d['orang_id'] ?: null,
            ':n' => $d['nama'],
            ':e' => mb_strtolower(trim($d['email']), 'UTF-8'),
            ':h' => $d['password_hash'],
            ':r' => $d['role_id'],
            ':a' => $d['is_aktif'] ?? 1,
            ':m' => $d['must_reset'] ?? 1,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $d): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET orang_id=:o, nama=:n, email=:e, role_id=:r, is_aktif=:a WHERE id=:id'
        );
        $stmt->execute([
            ':o' => $d['orang_id'] ?: null,
            ':n' => $d['nama'],
            ':e' => mb_strtolower(trim($d['email']), 'UTF-8'),
            ':r' => $d['role_id'],
            ':a' => $d['is_aktif'] ?? 1,
            ':id' => $id,
        ]);
    }

    public function updatePassword(int $id, string $hash, bool $mustReset = false): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash=:h, must_reset=:m WHERE id=:id');
        $stmt->execute([':h' => $hash, ':m' => $mustReset ? 1 : 0, ':id' => $id]);
    }

    public function setAktif(int $id, bool $aktif): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET is_aktif=:a WHERE id=:id');
        $stmt->execute([':a' => $aktif ? 1 : 0, ':id' => $id]);
    }

    public function touchLogin(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET last_login_at=NOW() WHERE id=:id');
        $stmt->execute([':id' => $id]);
    }

    /** CTE + window: rekap user per peran (gantikan hitung manual Excel). */
    public function countByRole(): array
    {
        $sql = <<<'SQL'
            WITH r AS (
              SELECT r.code, r.label, COUNT(u.id) AS jml
              FROM roles r LEFT JOIN users u ON u.role_id=r.id AND u.is_aktif=1
              GROUP BY r.code, r.label
            )
            SELECT code, label, jml,
              SUM(jml) OVER () AS total,
              RANK() OVER (ORDER BY jml DESC) AS peringkat
            FROM r ORDER BY jml DESC
            SQL;
        return $this->pdo->query($sql)->fetchAll();
    }
}
