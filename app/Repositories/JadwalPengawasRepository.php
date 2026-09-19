<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class JadwalPengawasRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** Seluruh jadwal 1 periode urut tanggal. */
    public function allByPeriode(int $periodeId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT j.*, o.nama AS orang_nama
             FROM jadwal_pengawas_pengolahan j
             LEFT JOIN orang o ON o.id = j.orang_id
             WHERE j.periode_id = :p
             ORDER BY j.tanggal ASC'
        );
        $stmt->execute([':p' => $periodeId]);
        return $stmt->fetchAll();
    }

    /** Jadwal pada tanggal tertentu (Y-m-d). */
    public function findByTanggal(int $periodeId, string $tanggal): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT j.*, o.nama AS orang_nama
             FROM jadwal_pengawas_pengolahan j
             LEFT JOIN orang o ON o.id = j.orang_id
             WHERE j.periode_id = :p AND j.tanggal = :t LIMIT 1'
        );
        $stmt->execute([':p' => $periodeId, ':t' => $tanggal]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** Pengawas bertugas hari ini (tanggal kalender aktif). NULL bila LIBUR / di luar rentang. */
    public function pengawasHariIni(int $periodeId, ?string $tanggal = null): ?array
    {
        $tgl = $tanggal ?? date('Y-m-d');
        $row = $this->findByTanggal($periodeId, $tgl);
        if ($row === null || ($row['status'] ?? '') !== 'TUGAS') {
            return null;
        }
        return $row;
    }

    public function countByPeriode(int $periodeId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM jadwal_pengawas_pengolahan WHERE periode_id = :p');
        $stmt->execute([':p' => $periodeId]);
        return (int) $stmt->fetchColumn();
    }
}
