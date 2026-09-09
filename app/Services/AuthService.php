<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditRepository;
use App\Repositories\UserRepository;
use PDO;

final class AuthService
{
    public function __construct(
        private PDO $pdo,
        private UserRepository $users,
        private AuditRepository $audit,
    ) {
    }

    /** @return array{ok:bool,error?:string,user?:array<string,mixed>} */
    public function attempt(string $email, string $password, string $ip, string $ua): array
    {
        $row = $this->users->findByEmail($email);
        // samakan waktu respon + pesan generik agar tidak bocorkan email terdaftar
        if ($row === null || (int) $row['is_aktif'] !== 1) {
            password_verify($password, password_hash('dummy', PASSWORD_ARGON2ID));
            return ['ok' => false, 'error' => 'Email atau password salah.'];
        }
        if (!password_verify($password, $row['password_hash'])) {
            $this->audit->log((int) $row['id'], 'LOGIN', 'users', (string) $row['id'], null, ['hasil' => 'gagal'], $ip, $ua);
            if (password_needs_rehash($row['password_hash'], PASSWORD_ARGON2ID)) {
                // jangan rehash sebelum verifikasi sukses
            }
            return ['ok' => false, 'error' => 'Email atau password salah.'];
        }
        // rehash diam-diam jika parameter Argon2 berubah (misal migrasi bcrypt pandalungan)
        if (password_needs_rehash($row['password_hash'], PASSWORD_ARGON2ID)) {
            $new = password_hash($password, PASSWORD_ARGON2ID);
            if ($new !== false) {
                $this->users->updatePassword((int) $row['id'], $new, (int) $row['must_reset'] === 1);
            }
        }
        $this->users->touchLogin((int) $row['id']);
        $this->audit->log((int) $row['id'], 'LOGIN', 'users', (string) $row['id'], null, ['hasil' => 'sukses'], $ip, $ua);
        unset($row['password_hash']);
        return ['ok' => true, 'user' => $row];
    }

    /** @return array{ok:bool,error?:string} */
    public function changeOwnPassword(int $userId, string $old, string $new): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id=:id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();
        if ($row === false) {
            return ['ok' => false, 'error' => 'Akun tidak ditemukan.'];
        }
        if (!password_verify($old, $row['password_hash'])) {
            return ['ok' => false, 'error' => 'Password lama salah.'];
        }
        if (strlen($new) < 8) {
            return ['ok' => false, 'error' => 'Password baru minimal 8 karakter.'];
        }
        $hash = password_hash($new, PASSWORD_ARGON2ID);
        if ($hash === false) {
            return ['ok' => false, 'error' => 'Gagal mengenkripsi password.'];
        }
        $this->users->updatePassword($userId, $hash, false);
        return ['ok' => true];
    }
}
