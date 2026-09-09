<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AuditRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function log(?int $userId, string $aksi, string $tabel, ?string $idTarget, mixed $before, mixed $after, string $ip, string $ua): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO audit_logs (user_id, aksi, tabel_target, id_target, before_json, after_json, ip_address, user_agent)
             VALUES (:u,:a,:t,:i,:b,:c,:ip,:ua)'
        );
        $stmt->execute([
            ':u' => $userId,
            ':a' => $aksi,
            ':t' => $tabel,
            ':i' => $idTarget,
            ':b' => $before === null ? null : json_encode($before, JSON_UNESCAPED_UNICODE),
            ':c' => $after === null ? null : json_encode($after, JSON_UNESCAPED_UNICODE),
            ':ip' => $ip,
            ':ua' => $ua,
        ]);
    }

    /** @return array{data:array<int,array<string,mixed>>,total:int} */
    public function paginate(int $page, int $perPage, string $tabel = 'all'): array
    {
        $w = '';
        $p = [];
        if ($tabel !== 'all') {
            $w = 'WHERE l.tabel_target=:t';
            $p[':t'] = $tabel;
        }
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM audit_logs l {$w}");
        $stmt->execute($p);
        $total = (int) $stmt->fetchColumn();
        $offset = max(0, ($page - 1) * $perPage);
        $stmt = $this->pdo->prepare(
            "SELECT l.*, u.nama AS nama_user FROM audit_logs l LEFT JOIN users u ON u.id=l.user_id
             {$w} ORDER BY l.id DESC LIMIT :l OFFSET :o"
        );
        foreach ($p as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':l', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':o', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }
}
