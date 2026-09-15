<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Validator;
use App\Repositories\AuditRepository;
use App\Repositories\OrangRepository;
use PDO;

final class OrangService
{
    public function __construct(
        private PDO $pdo,
        private OrangRepository $orang,
        private AuditRepository $audit,
    ) {
    }

    /** @return array{ok:bool,errors:array<string,string>,id?:int} */
    public function create(array $in, ?int $actorId, string $ip, string $ua): array
    {
        $in['nama'] = Validator::canonicalNama((string) ($in['nama'] ?? ''));
        $errors = Validator::orang($in);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }
        $norm = Validator::normalized($in['nama']);
        if ($this->orang->findByNormalized($norm) !== null) {
            return ['ok' => false, 'errors' => ['nama' => 'Nama sudah terdaftar (cek juga varian di alias).']];
        }
        $this->pdo->beginTransaction();
        try {
            $id = $this->orang->create([
                'nama' => $in['nama'],
                'no_hp' => trim((string) ($in['no_hp'] ?? '')),
                'email' => trim((string) ($in['email'] ?? '')),
                'alamat' => trim((string) ($in['alamat'] ?? '')),
                'role_id' => !empty($in['role_id']) ? (int) $in['role_id'] : null,
                'is_aktif' => isset($in['is_aktif']) ? (int) $in['is_aktif'] : 1,
            ]);
            $this->audit->log($actorId, 'CREATE', 'orang', (string) $id, null, $this->orang->find($id), $ip, $ua);
            $this->pdo->commit();
            return ['ok' => true, 'errors' => [], 'id' => $id];
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            if (str_contains($e->getMessage(), 'uq_orang_nama') || str_contains($e->getMessage(), 'Duplicate')) {
                return ['ok' => false, 'errors' => ['nama' => 'Nama sudah terdaftar.']];
            }
            throw $e;
        }
    }

    /** @return array{ok:bool,errors:array<string,string>} */
    public function update(int $id, array $in, ?int $actorId, string $ip, string $ua): array
    {
        $row = $this->orang->find($id);
        if ($row === null) {
            return ['ok' => false, 'errors' => ['nama' => 'Data tidak ditemukan.']];
        }
        $in['nama'] = Validator::canonicalNama((string) ($in['nama'] ?? ''));
        $errors = Validator::orang($in);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }
        $norm = Validator::normalized($in['nama']);
        $dupe = $this->orang->findByNormalized($norm);
        if ($dupe !== null && (int) $dupe['id'] !== $id) {
            return ['ok' => false, 'errors' => ['nama' => 'Nama dipakai orang lain.']];
        }
        $this->pdo->beginTransaction();
        try {
            $before = $row;
            $this->orang->update($id, [
                'nama' => $in['nama'],
                'no_hp' => trim((string) ($in['no_hp'] ?? '')),
                'email' => trim((string) ($in['email'] ?? '')),
                'alamat' => trim((string) ($in['alamat'] ?? '')),
                'role_id' => array_key_exists('role_id', $in) ? (!empty($in['role_id']) ? (int) $in['role_id'] : null) : (!empty($row['role_id']) ? (int) $row['role_id'] : null),
                'is_aktif' => isset($in['is_aktif']) ? (int) $in['is_aktif'] : (int) $row['is_aktif'],
            ]);
            $this->audit->log($actorId, 'UPDATE', 'orang', (string) $id, $before, $this->orang->find($id), $ip, $ua);
            $this->pdo->commit();
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function setAktif(int $id, bool $aktif, ?int $actorId, string $ip, string $ua): bool
    {
        $row = $this->orang->find($id);
        if ($row === null) {
            return false;
        }
        $this->pdo->beginTransaction();
        try {
            $this->orang->setAktif($id, $aktif);
            $this->audit->log($actorId, 'UPDATE', 'orang', (string) $id, $row, $this->orang->find($id), $ip, $ua);
            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /** @return array{ok:bool,error?:string} */
    public function addAlias(int $orangId, string $aliasBebas, ?int $actorId, string $ip, string $ua): array
    {
        $norm = Validator::normalized($aliasBebas);
        if ($norm === '' || mb_strlen($norm) < 3) {
            return ['ok' => false, 'error' => 'Alias minimal 3 huruf.'];
        }
        // alias tidak boleh menabrak nama orang lain / alias orang lain (UNIQUE global)
        $tabrak = $this->orang->resolveAlias($norm);
        if ($tabrak !== null && (int) $tabrak['id'] !== $orangId) {
            return ['ok' => false, 'error' => 'Alias sudah dipakai: ' . $tabrak['nama']];
        }
        try {
            $this->orang->addAlias($orangId, $norm);
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Duplicate')) {
                return ['ok' => false, 'error' => 'Alias sudah ada.'];
            }
            throw $e;
        }
        $this->audit->log($actorId, 'CREATE', 'orang_alias', null, null, ['orang_id' => $orangId, 'alias' => $norm], $ip, $ua);
        return ['ok' => true];
    }
}
