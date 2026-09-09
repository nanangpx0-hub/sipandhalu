<?php

declare(strict_types=1);

namespace App\Core;

/** Helper kode BPS: pad digit, pecah kode SLS 16 digit (split 4+2). */
final class KodeBps
{
    public static function pad(string $s, int $len): string
    {
        $s = trim($s);
        if (!ctype_digit($s)) {
            return $s;
        }
        return str_pad($s, $len, '0', STR_PAD_LEFT);
    }

    public static function isKode(string $s, int $len): bool
    {
        return (bool) preg_match('/^[0-9]{' . $len . '}$/', trim($s));
    }

    /**
     * Pecah 16 digit -> prov2+kab2+kec3+desa3+sls4+sub2.
     * @return array<string,string>|null
     */
    public static function pecah16(string $kode): ?array
    {
        $k = trim($kode);
        if (!preg_match('/^[0-9]{16}$/', $k)) {
            return null;
        }
        return [
            'prov' => substr($k, 0, 2),
            'kab' => substr($k, 2, 2),
            'kec' => substr($k, 4, 3),
            'desa' => substr($k, 7, 3),
            'sls' => substr($k, 10, 4),
            'sub' => substr($k, 14, 2),
        ];
    }

    public static function rakit16(string $prov, string $kab, string $kec, string $desa, string $sls, string $sub): string
    {
        return $prov . $kab . $kec . $desa . $sls . $sub;
    }
}
