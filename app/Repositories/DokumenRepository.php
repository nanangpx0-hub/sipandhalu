<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class DokumenRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function catatPinjam(
        int $sampelId,
        string $jenisDok,
        int $peminjamId,
        string $peminjamPeran,
        string $waktuPinjam,
        string $alasan,
        int $operatorPinjamId
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO peminjaman_dokumen 
             (sampel_id, jenis_dok, peminjam_id, peminjam_peran, waktu_pinjam, alasan, operator_pinjam_id, status)
             VALUES (:s, :j, :p, :pr, :w, :a, :op, "DIPINJAM")'
        );
        $stmt->execute([
            ':s' => $sampelId,
            ':j' => $jenisDok,
            ':p' => $peminjamId,
            ':pr' => $peminjamPeran,
            ':w' => $waktuPinjam,
            ':a' => $alasan,
            ':op' => $operatorPinjamId,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function catatKembali(
        int $pinjamId,
        int $operatorKembaliId,
        string $waktuKembali,
        ?string $catatanKembali
    ): void {
        $stmt = $this->pdo->prepare(
            'UPDATE peminjaman_dokumen
             SET status = "DIKEMBALIKAN",
                 operator_kembali_id = :op,
                 waktu_kembali = :w,
                 catatan_kembali = :c
             WHERE id = :id'
        );
        $stmt->execute([
            ':op' => $operatorKembaliId,
            ':w' => $waktuKembali,
            ':c' => $catatanKembali,
            ':id' => $pinjamId,
        ]);
    }

    public function findPinjam(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM peminjaman_dokumen WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Ambil peminjaman aktif (status = 'DIPINJAM') untuk sampel tertentu.
     * @return array<int,array<string,mixed>>
     */
    public function activePinjamBySampel(int $sampelId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT pd.*, o.nama AS peminjam_nama, u.nama AS operator_nama
             FROM peminjaman_dokumen pd
             LEFT JOIN orang o ON o.id = pd.peminjam_id
             LEFT JOIN users u ON u.id = pd.operator_pinjam_id
             WHERE pd.sampel_id = :s AND pd.status = "DIPINJAM"
             ORDER BY pd.waktu_pinjam DESC'
        );
        $stmt->execute([':s' => $sampelId]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil seluruh riwayat peminjaman untuk sampel tertentu.
     * @return array<int,array<string,mixed>>
     */
    public function riwayatBySampel(int $sampelId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT pd.*, o.nama AS peminjam_nama,
                    u1.nama AS operator_pinjam_nama,
                    u2.nama AS operator_kembali_nama
             FROM peminjaman_dokumen pd
             LEFT JOIN orang o ON o.id = pd.peminjam_id
             LEFT JOIN users u1 ON u1.id = pd.operator_pinjam_id
             LEFT JOIN users u2 ON u2.id = pd.operator_kembali_id
             WHERE pd.sampel_id = :s
             ORDER BY pd.created_at DESC'
        );
        $stmt->execute([':s' => $sampelId]);
        return $stmt->fetchAll();
    }
}
