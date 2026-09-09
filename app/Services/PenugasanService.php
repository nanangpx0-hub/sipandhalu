<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditRepository;
use App\Repositories\SampelRepository;
use PDO;

final class PenugasanService
{
    public function __construct(
        private PDO $pdo,
        private SampelRepository $sampel,
        private AuditRepository $audit,
    ) {
    }

    /**
     * Validasi K4: 3 peran 1 sampel wajib orang berbeda, dan tiap orang
     * hanya boleh 1 peran dalam 1 periode (boleh banyak NKS peran sama).
     * @return array<string,string> errors
     */
    public function validate(int $periodeId, int $sampelId, int $pcl, int $pml, int $pengolah): array
    {
        $e = [];
        if ($pcl === $pml || $pcl === $pengolah || $pml === $pengolah) {
            $e['petugas'] = 'PCL, PML, Pengolah dalam 1 SLS wajib 3 orang berbeda.';
            return $e;
        }
        // peran lama orang di periode ini (di luar sampel yg sedang diedit)
        foreach (['PCL' => $pcl, 'PML' => $pml, 'PENGOLAH' => $pengolah] as $peranBaru => $orangId) {
            $punya = $this->sampel->peranOrangDiPeriode($periodeId, $orangId);
            // kecualikan sampel saat ini: cek apakah satu-satunya barisnya ya sampel ini
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*) FROM penugasan pg JOIN sampel sp ON sp.id=pg.sampel_id
                 WHERE sp.periode_id=:p AND (pg.pcl_id=:o1 OR pg.pml_id=:o2 OR pg.pengolah_id=:o3)
                 AND pg.sampel_id<>:s'
            );
            $stmt->execute([':p' => $periodeId, ':o1' => $orangId, ':o2' => $orangId, ':o3' => $orangId, ':s' => $sampelId]);
            $diSampelLain = (int) $stmt->fetchColumn() > 0;
            if ($diSampelLain) {
                foreach ($punya as $peranLama) {
                    if ($peranLama !== $peranBaru) {
                        $e['petugas'] = "Orang #$orangId sudah jadi $peranLama di periode ini; tidak boleh rangkap $peranBaru (K4).";
                        break 2;
                    }
                }
            }
        }
        return $e;
    }

    /** @return array{ok:bool,errors:array<string,string>} */
    public function assign(int $periodeId, int $sampelId, int $pcl, int $pml, int $pengolah, ?int $actor, string $ip, string $ua): array
    {
        // periode TUTUP tidak boleh diubah
        $st = $this->pdo->prepare('SELECT status FROM periode WHERE id=:p');
        $st->execute([':p' => $periodeId]);
        if ($st->fetchColumn() === 'TUTUP') {
            return ['ok' => false, 'errors' => ['periode' => 'Periode sudah TUTUP (read-only).']];
        }
        $e = $this->validate($periodeId, $sampelId, $pcl, $pml, $pengolah);
        if ($e !== []) {
            return ['ok' => false, 'errors' => $e];
        }
        $this->pdo->beginTransaction();
        try {
            $this->sampel->assign($sampelId, $pcl, $pml, $pengolah);
            $this->audit->log($actor, 'UPDATE', 'penugasan', (string) $sampelId, null, [
                'sampel_id' => $sampelId, 'pcl' => $pcl, 'pml' => $pml, 'pengolah' => $pengolah,
            ], $ip, $ua);
            $this->pdo->commit();
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable $ex) {
            $this->pdo->rollBack();
            throw $ex;
        }
    }
}
