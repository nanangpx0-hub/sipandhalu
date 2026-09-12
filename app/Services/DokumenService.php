<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditRepository;
use App\Repositories\DokumenRepository;
use App\Repositories\SampelRepository;
use InvalidArgumentException;
use PDO;
use RuntimeException;

final class DokumenService
{
    public function __construct(
        private PDO $pdo,
        private SampelRepository $sampelRepo,
        private DokumenRepository $dokumenRepo,
        private AuditRepository $auditRepo
    ) {
    }

    private function getPeriodeStatus(int $periodeId): string
    {
        $stmt = $this->pdo->prepare('SELECT status FROM periode WHERE id = :p');
        $stmt->execute([':p' => $periodeId]);
        $status = $stmt->fetchColumn();
        if ($status === false) {
            throw new InvalidArgumentException('Periode tidak ditemukan.');
        }
        return (string) $status;
    }

    /**
     * Penerimaan berkas satuan (per NKS).
     */
    public function terimaSatuan(
        int $sampelId,
        array $payload,
        int $operatorId,
        string $ip = '',
        string $userAgent = ''
    ): void {
        $sampel = $this->sampelRepo->find($sampelId);
        if ($sampel === null) {
            throw new InvalidArgumentException('Sampel tidak ditemukan.');
        }

        $pStatus = $this->getPeriodeStatus((int) $sampel['periode_id']);
        if ($pStatus !== 'AKTIF') {
            throw new RuntimeException("Penerimaan dokumen hanya dapat dilakukan pada periode AKTIF (status saat ini: {$pStatus}).");
        }

        $terimaPemutakhiran = !empty($payload['terima_pemutakhiran']);
        $terimaPeta = !empty($payload['terima_peta']);
        $penyerahId = !empty($payload['penyerah_id']) ? (int) $payload['penyerah_id'] : null;
        $waktuTerima = !empty($payload['waktu_terima']) ? trim((string) $payload['waktu_terima']) : date('Y-m-d H:i:s');
        $hasilKk = isset($payload['hasil_kk']) && $payload['hasil_kk'] !== '' ? (int) $payload['hasil_kk'] : (isset($sampel['hasil_kk']) ? (int) $sampel['hasil_kk'] : null);
        $hasilRt = isset($payload['hasil_rt']) && $payload['hasil_rt'] !== '' ? (int) $payload['hasil_rt'] : (isset($sampel['hasil_rt']) ? (int) $sampel['hasil_rt'] : null);
        $catatan = isset($payload['catatan']) ? trim((string) $payload['catatan']) : null;

        $fields = [
            'hasil_kk' => $hasilKk,
            'hasil_rt' => $hasilRt,
            'catatan_dokumen' => $catatan,
        ];

        if ($terimaPemutakhiran) {
            $fields['dok_pemutakhiran_status'] = 'DITERIMA';
            $fields['dok_pemutakhiran_waktu'] = $waktuTerima;
            $fields['dok_pemutakhiran_oleh'] = $operatorId;
            $fields['dok_pemutakhiran_penyerah'] = $penyerahId;
            $fields['dokumen_vsen'] = 1;
        }

        if ($terimaPeta) {
            $fields['peta_status'] = 'DITERIMA';
            $fields['peta_waktu'] = $waktuTerima;
            $fields['peta_oleh'] = $operatorId;
            $fields['peta_penyerah'] = $penyerahId;
            $fields['peta_ws'] = 1;
        }

        $this->pdo->beginTransaction();
        try {
            $this->sampelRepo->updateDokumenFisik($sampelId, $fields);
            $this->auditRepo->log(
                $operatorId,
                'UPDATE',
                'sampel',
                (string) $sampelId,
                [
                    'dok_pemutakhiran' => $sampel['dok_pemutakhiran_status'],
                    'peta' => $sampel['peta_status'],
                ],
                $fields,
                $ip,
                $userAgent
            );
            $this->pdo->commit();
        } catch (\Throwable $t) {
            $this->pdo->rollBack();
            throw $t;
        }
    }

    /**
     * Penerimaan kolektif berdasarkan PML.
     * @param array<int,array{sampel_id:int,pemutakhiran?:bool,peta?:bool,hasil_kk?:int|null,hasil_rt?:int|null}> $items
     */
    public function terimaKolektif(
        int $periodeId,
        int $pmlId,
        array $items,
        string $waktuTerima,
        ?string $catatan,
        int $operatorId,
        string $ip = '',
        string $userAgent = ''
    ): int {
        $pStatus = $this->getPeriodeStatus($periodeId);
        if ($pStatus !== 'AKTIF') {
            throw new RuntimeException("Penerimaan dokumen hanya dapat dilakukan pada periode AKTIF (status saat ini: {$pStatus}).");
        }

        $this->pdo->beginTransaction();
        $processed = 0;
        try {
            foreach ($items as $item) {
                $sampelId = (int) ($item['sampel_id'] ?? 0);
                if ($sampelId <= 0) {
                    continue;
                }

                $sampel = $this->sampelRepo->find($sampelId);
                if ($sampel === null || (int) $sampel['periode_id'] !== $periodeId) {
                    continue;
                }

                $fields = [];
                if (!empty($item['pemutakhiran'])) {
                    $fields['dok_pemutakhiran_status'] = 'DITERIMA';
                    $fields['dok_pemutakhiran_waktu'] = $waktuTerima;
                    $fields['dok_pemutakhiran_oleh'] = $operatorId;
                    $fields['dok_pemutakhiran_penyerah'] = $pmlId;
                    $fields['dokumen_vsen'] = 1;
                }
                if (!empty($item['peta'])) {
                    $fields['peta_status'] = 'DITERIMA';
                    $fields['peta_waktu'] = $waktuTerima;
                    $fields['peta_oleh'] = $operatorId;
                    $fields['peta_penyerah'] = $pmlId;
                    $fields['peta_ws'] = 1;
                }
                if (isset($item['hasil_kk']) && $item['hasil_kk'] !== '') {
                    $fields['hasil_kk'] = (int) $item['hasil_kk'];
                }
                if (isset($item['hasil_rt']) && $item['hasil_rt'] !== '') {
                    $fields['hasil_rt'] = (int) $item['hasil_rt'];
                }
                if ($catatan !== null && $catatan !== '') {
                    $fields['catatan_dokumen'] = $catatan;
                }

                if (!empty($fields)) {
                    $this->sampelRepo->updateDokumenFisik($sampelId, $fields);
                    $this->auditRepo->log(
                        $operatorId,
                        'UPDATE',
                        'sampel',
                        (string) $sampelId,
                        ['kolektif_pml' => $pmlId],
                        $fields,
                        $ip,
                        $userAgent
                    );
                    $processed++;
                }
            }
            $this->pdo->commit();
            return $processed;
        } catch (\Throwable $t) {
            $this->pdo->rollBack();
            throw $t;
        }
    }

    /**
     * Catat peminjaman dokumen fisik.
     */
    public function pinjamDokumen(
        int $sampelId,
        string $jenisDok,
        int $peminjamId,
        string $peminjamPeran,
        string $waktuPinjam,
        string $alasan,
        int $operatorId,
        string $ip = '',
        string $userAgent = ''
    ): int {
        $sampel = $this->sampelRepo->find($sampelId);
        if ($sampel === null) {
            throw new InvalidArgumentException('Sampel tidak ditemukan.');
        }

        $pStatus = $this->getPeriodeStatus((int) $sampel['periode_id']);
        if ($pStatus !== 'AKTIF') {
            throw new RuntimeException("Peminjaman dokumen hanya dapat dilakukan pada periode AKTIF.");
        }

        if (!in_array($jenisDok, ['SEMUA', 'PEMUTAKHIRAN', 'PETA'], true)) {
            throw new InvalidArgumentException('Jenis dokumen yang dipinjam tidak valid.');
        }

        if (trim($alasan) === '') {
            throw new InvalidArgumentException('Alasan peminjaman wajib diisi.');
        }

        // Verifikasi bahwa dokumen yang dipinjam memang sudah DITERIMA di kantor
        if (($jenisDok === 'PEMUTAKHIRAN' || $jenisDok === 'SEMUA') && ($sampel['dok_pemutakhiran_status'] ?? '') !== 'DITERIMA') {
            throw new RuntimeException('Dokumen pemutakhiran belum berstatus DITERIMA di kantor atau sedang dipinjam.');
        }
        if (($jenisDok === 'PETA' || $jenisDok === 'SEMUA') && ($sampel['peta_status'] ?? '') !== 'DITERIMA') {
            throw new RuntimeException('Peta Wilkerstat belum berstatus DITERIMA di kantor atau sedang dipinjam.');
        }

        $this->pdo->beginTransaction();
        try {
            $pinjamId = $this->dokumenRepo->catatPinjam(
                $sampelId,
                $jenisDok,
                $peminjamId,
                $peminjamPeran,
                $waktuPinjam,
                $alasan,
                $operatorId
            );

            $sampelFields = [];
            if ($jenisDok === 'PEMUTAKHIRAN' || $jenisDok === 'SEMUA') {
                $sampelFields['dok_pemutakhiran_status'] = 'DIPINJAM';
            }
            if ($jenisDok === 'PETA' || $jenisDok === 'SEMUA') {
                $sampelFields['peta_status'] = 'DIPINJAM';
            }
            $this->sampelRepo->updateDokumenFisik($sampelId, $sampelFields);

            $this->auditRepo->log(
                $operatorId,
                'CREATE',
                'peminjaman_dokumen',
                (string) $pinjamId,
                null,
                [
                    'sampel_id' => $sampelId,
                    'jenis_dok' => $jenisDok,
                    'peminjam_id' => $peminjamId,
                    'alasan' => $alasan,
                ],
                $ip,
                $userAgent
            );

            $this->pdo->commit();
            return $pinjamId;
        } catch (\Throwable $t) {
            $this->pdo->rollBack();
            throw $t;
        }
    }

    /**
     * Pengembalian dokumen fisik yang dipinjam.
     */
    public function kembalikanDokumen(
        int $pinjamId,
        string $waktuKembali,
        ?string $catatan,
        int $operatorId,
        string $ip = '',
        string $userAgent = ''
    ): void {
        $pinjam = $this->dokumenRepo->findPinjam($pinjamId);
        if ($pinjam === null) {
            throw new InvalidArgumentException('Data peminjaman tidak ditemukan.');
        }

        if ($pinjam['status'] !== 'DIPINJAM') {
            throw new RuntimeException('Dokumen ini sudah berstatus DIKEMBALIKAN sebelumnya.');
        }

        $sampelId = (int) $pinjam['sampel_id'];
        $sampel = $this->sampelRepo->find($sampelId);
        if ($sampel === null) {
            throw new InvalidArgumentException('Sampel tidak ditemukan.');
        }

        $this->pdo->beginTransaction();
        try {
            $this->dokumenRepo->catatKembali($pinjamId, $operatorId, $waktuKembali, $catatan);

            $jenisDok = (string) $pinjam['jenis_dok'];
            $sampelFields = [];
            if ($jenisDok === 'PEMUTAKHIRAN' || $jenisDok === 'SEMUA') {
                $sampelFields['dok_pemutakhiran_status'] = 'DITERIMA';
            }
            if ($jenisDok === 'PETA' || $jenisDok === 'SEMUA') {
                $sampelFields['peta_status'] = 'DITERIMA';
            }
            $this->sampelRepo->updateDokumenFisik($sampelId, $sampelFields);

            $this->auditRepo->log(
                $operatorId,
                'UPDATE',
                'peminjaman_dokumen',
                (string) $pinjamId,
                ['status' => 'DIPINJAM'],
                [
                    'status' => 'DIKEMBALIKAN',
                    'waktu_kembali' => $waktuKembali,
                    'catatan_kembali' => $catatan,
                ],
                $ip,
                $userAgent
            );

            $this->pdo->commit();
        } catch (\Throwable $t) {
            $this->pdo->rollBack();
            throw $t;
        }
    }
}
