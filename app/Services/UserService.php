<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Validator;
use App\Repositories\AuditRepository;
use App\Repositories\UserRepository;
use PDO;

final class UserService
{
    public function __construct(
        private PDO $pdo,
        private UserRepository $users,
        private AuditRepository $audit,
    ) {
    }

    /** @return array{ok:bool,errors:array<string,string>,id?:int} */
    public function create(array $in, ?int $actorId, string $ip, string $ua): array
    {
        $errors = Validator::userCreate($in);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }
        if ($this->users->emailExists($in['email'])) {
            return ['ok' => false, 'errors' => ['email' => 'Email sudah dipakai akun lain.']];
        }
        $hash = password_hash((string) $in['password'], PASSWORD_ARGON2ID);
        if ($hash === false) {
            return ['ok' => false, 'errors' => ['password' => 'Gagal mengenkripsi password.']];
        }
        $this->pdo->beginTransaction();
        try {
            $id = $this->users->create([
                'orang_id' => $in['orang_id'] ?? null,
                'nama' => Validator::canonicalNama((string) $in['nama']),
                'email' => $in['email'],
                'password_hash' => $hash,
                'role_id' => (int) $in['role_id'],
                'is_aktif' => isset($in['is_aktif']) ? (int) $in['is_aktif'] : 1,
                'must_reset' => 0,
            ]);
            $row = $this->users->find($id);
            unset($row['password_hash']);
            $this->audit->log($actorId, 'CREATE', 'users', (string) $id, null, $row, $ip, $ua);
            $this->pdo->commit();
            return ['ok' => true, 'errors' => [], 'id' => $id];
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /** @return array{ok:bool,errors:array<string,string>} */
    public function update(int $id, array $in, ?int $actorId, string $ip, string $ua): array
    {
        $row = $this->users->find($id);
        if ($row === null) {
            return ['ok' => false, 'errors' => ['nama' => 'Akun tidak ditemukan.']];
        }
        $errors = Validator::userUpdate($in);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }
        if ($this->users->emailExists($in['email'], $id)) {
            return ['ok' => false, 'errors' => ['email' => 'Email dipakai akun lain.']];
        }
        $this->pdo->beginTransaction();
        try {
            $before = $row;
            unset($before['password_hash']);
            $this->users->update($id, [
                'orang_id' => $in['orang_id'] ?? $row['orang_id'],
                'nama' => Validator::canonicalNama((string) $in['nama']),
                'email' => $in['email'],
                'role_id' => (int) ($in['role_id'] ?? $row['role_id']),
                'is_aktif' => isset($in['is_aktif']) ? (int) $in['is_aktif'] : (int) $row['is_aktif'],
            ]);
            $pw = (string) ($in['password'] ?? '');
            if ($pw !== '') {
                $hash = password_hash($pw, PASSWORD_ARGON2ID);
                if ($hash === false) {
                    $this->pdo->rollBack();
                    return ['ok' => false, 'errors' => ['password' => 'Gagal mengenkripsi password.']];
                }
                $this->users->updatePassword($id, $hash, false);
            }
            $after = $this->users->find($id);
            unset($after['password_hash']);
            $this->audit->log($actorId, 'UPDATE', 'users', (string) $id, $before, $after, $ip, $ua);
            $this->pdo->commit();
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function resetPassword(int $id, ?int $actorId, string $ip, string $ua, int $len = 10): ?string
    {
        $row = $this->users->find($id);
        if ($row === null) {
            return null;
        }
        // password sementara aman; user wajib ganti saat login (must_reset=1)
        $plain = substr(str_replace(['/', '+', '='], '', base64_encode(random_bytes(16))), 0, $len) . '9aA!';
        $hash = password_hash($plain, PASSWORD_ARGON2ID);
        if ($hash === false) {
            return null;
        }
        $this->pdo->beginTransaction();
        try {
            $this->users->updatePassword($id, $hash, true);
            $this->audit->log($actorId, 'RESET_PW', 'users', (string) $id, ['email' => $row['email']], ['email' => $row['email'], 'must_reset' => 1], $ip, $ua);
            $this->pdo->commit();
            return $plain;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function setAktif(int $id, bool $aktif, ?int $actorId, string $ip, string $ua, ?int $selfId): bool
    {
        if ($selfId !== null && $id === $selfId && !$aktif) {
            return false; // cegah nonaktifkan diri sendiri
        }
        $row = $this->users->find($id);
        if ($row === null) {
            return false;
        }
        $this->pdo->beginTransaction();
        try {
            $this->users->setAktif($id, $aktif);
            $this->audit->log($actorId, 'UPDATE', 'users', (string) $id, ['is_aktif' => $row['is_aktif']], ['is_aktif' => $aktif ? 1 : 0], $ip, $ua);
            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
