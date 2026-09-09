<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\KodeBps;
use App\Repositories\AuditRepository;
use App\Repositories\DesaRepository;
use App\Repositories\SlsRepository;
use App\Repositories\WilayahRepository;
use PDO;

final class SlsService
{
    public function __construct(
        private PDO $pdo,
        private SlsRepository $sls,
        private DesaRepository $desa,
        private WilayahRepository $wil,
        private AuditRepository $audit,
    ) {
    }

    /**
     * Normalisasi input form SLS. 2 cara: kode_full 16 digit, atau kec+desa+sls4+sub2.
     * @return array{ok:bool,errors:array<string,string>,data?:array<string,mixed>}
     */
    public function normalize(array $in, ?int $ignoreId = null): array
    {
        $e = [];
        $d = [
            'kode_full' => trim((string) ($in['kode_full'] ?? '')),
            'kec' => KodeBps::pad((string) ($in['kec'] ?? ''), 3),
            'desa' => KodeBps::pad((string) ($in['desa'] ?? ''), 3),
            'sls' => trim((string) ($in['sls'] ?? '')),
            'sub' => trim((string) ($in['sub'] ?? '')),
            'nks' => KodeBps::pad((string) ($in['nks'] ?? ''), 5),
            'desa_id' => $in['desa_id'] ?? '',
            'dusun' => trim((string) ($in['dusun'] ?? '')),
            'rw' => trim((string) ($in['rw'] ?? '')),
            'rt' => trim((string) ($in['rt'] ?? '')),
            'nama_sls' => trim((string) ($in['nama_sls'] ?? '')),
            'ketua' => trim((string) ($in['ketua'] ?? '')),
            'klasifikasi' => $in['klasifikasi'] ?? '',
            'jml_kk' => $in['jml_kk'] ?? '',
            'jml_rt' => $in['jml_rt'] ?? '',
            'is_aktif' => isset($in['is_aktif']) ? 1 : 0,
            'prov' => '35', 'kab' => '09',
        ];
        if ($d['kode_full'] !== '') {
            $p = KodeBps::pecah16($d['kode_full']);
            if ($p === null) {
                $e['kode_full'] = 'Kode SLS harus 16 digit angka (cth 3509010001000100).';
            } else {
                $d = array_merge($d, $p);
            }
        } else {
            if (!KodeBps::isKode($d['kec'], 3)) {
                $e['kec'] = 'Kode kecamatan 3 digit.';
            }
            if (!KodeBps::isKode($d['desa'], 3)) {
                $e['desa'] = 'Kode desa 3 digit.';
            }
            if ($d['sls'] !== '' && !KodeBps::isKode($d['sls'], 4)) {
                $e['sls'] = 'Kode SLS 4 digit (cth 0042).';
            }
            if ($d['sub'] !== '' && !KodeBps::isKode($d['sub'], 2)) {
                $e['sub'] = 'Kode sub-SLS 2 digit (cth 00).';
            }
            if ($d['sls'] !== '' && $d['sub'] !== '') {
                $d['kode_full'] = KodeBps::rakit16('35', '09', $d['kec'], $d['desa'], $d['sls'], $d['sub']);
            }
        }
        if (!KodeBps::isKode($d['nks'], 5)) {
            $e['nks'] = 'NKS 5 digit angka (cth 56361).';
        }
        if (!isset($e['kec']) && $this->wil->findKecamatan($d['kec']) === null) {
            $e['kec'] = 'Kecamatan ' . $d['kec'] . ' belum ada di master.';
        }
        if (!isset($e['nks'])) {
            $ada = $this->sls->findByNks($d['nks']);
            if ($ada !== null && (int) $ada['id'] !== (int) ($ignoreId ?? 0)) {
                $e['nks'] = 'NKS sudah dipakai SLS lain.';
            }
        }
        if ($d['kode_full'] !== '' && !isset($e['kode_full'])) {
            $dupe = $this->sls->findByFull($d['kode_full']);
            if ($dupe !== null && (int) $dupe['id'] !== (int) ($ignoreId ?? 0)) {
                $e['kode_full'] = 'Kode 16 digit sudah dipakai SLS lain.';
            }
        }
        if ($e !== []) {
            return ['ok' => false, 'errors' => $e];
        }
        if ($d['desa_id'] === '') {
            $dd = $this->desa->findByKode($d['kec'], $d['desa']);
            $d['desa_id'] = $dd === null ? null : $dd['id'];
        }
        $d['klasifikasi'] = $d['klasifikasi'] === '' ? null : (int) $d['klasifikasi'];
        $d['jml_kk'] = $d['jml_kk'] === '' ? null : (int) $d['jml_kk'];
        $d['jml_rt'] = $d['jml_rt'] === '' ? null : (int) $d['jml_rt'];
        return ['ok' => true, 'errors' => [], 'data' => $d];
    }
}
