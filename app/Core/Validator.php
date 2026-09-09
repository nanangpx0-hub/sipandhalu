<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    /** @return array<string,string> errors keyed by field */
    public static function orang(array $in): array
    {
        $e = [];
        $nama = trim((string) ($in['nama'] ?? ''));
        if ($nama === '' || mb_strlen($nama) < 3) {
            $e['nama'] = 'Nama wajib diisi, minimal 3 huruf.';
        } elseif (mb_strlen($nama) > 100) {
            $e['nama'] = 'Nama maksimal 100 karakter.';
        }
        $hp = trim((string) ($in['no_hp'] ?? ''));
        if ($hp !== '' && !preg_match('/^[0-9+\-\s]{9,20}$/', $hp)) {
            $e['no_hp'] = 'No HP 9-20 karakter (angka, +, -, spasi).';
        }
        $email = trim((string) ($in['email'] ?? ''));
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $e['email'] = 'Format email tidak valid.';
        }
        return $e;
    }

    /** @return array<string,string> */
    public static function userCreate(array $in): array
    {
        $e = [];
        $nama = trim((string) ($in['nama'] ?? ''));
        if ($nama === '' || mb_strlen($nama) < 3) {
            $e['nama'] = 'Nama wajib diisi.';
        }
        if (filter_var(trim((string) ($in['email'] ?? '')), FILTER_VALIDATE_EMAIL) === false) {
            $e['email'] = 'Email tidak valid.';
        }
        $pw = (string) ($in['password'] ?? '');
        if (strlen($pw) < 8) {
            $e['password'] = 'Password minimal 8 karakter.';
        }
        if (empty($in['role_id']) || filter_var($in['role_id'], FILTER_VALIDATE_INT) === false) {
            $e['role_id'] = 'Peran wajib dipilih.';
        }
        if (isset($in['orang_id']) && $in['orang_id'] !== '' && filter_var($in['orang_id'], FILTER_VALIDATE_INT) === false) {
            $e['orang_id'] = 'Petugas tidak valid.';
        }
        return $e;
    }

    /** @return array<string,string> */
    public static function userUpdate(array $in): array
    {
        $e = [];
        $nama = trim((string) ($in['nama'] ?? ''));
        if ($nama === '' || mb_strlen($nama) < 3) {
            $e['nama'] = 'Nama wajib diisi.';
        }
        if (filter_var(trim((string) ($in['email'] ?? '')), FILTER_VALIDATE_EMAIL) === false) {
            $e['email'] = 'Email tidak valid.';
        }
        $pw = (string) ($in['password'] ?? '');
        if ($pw !== '' && strlen($pw) < 8) {
            $e['password'] = 'Password baru minimal 8 karakter (kosongkan jika tidak ganti).';
        }
        return $e;
    }

    public static function canonicalNama(string $nama): string
    {
        $nama = trim(preg_replace('/\s+/', ' ', $nama) ?? '');
        return mb_convert_case($nama, MB_CASE_TITLE, 'UTF-8');
    }

    public static function normalized(string $s): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $s) ?? ''), 'UTF-8');
    }
}
