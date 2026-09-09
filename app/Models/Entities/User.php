<?php

declare(strict_types=1);

namespace App\Models\Entities;

final class User
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $orangId,
        public readonly string $nama,
        public readonly string $email,
        public readonly string $roleCode,
        public readonly string $roleLabel,
        public readonly bool $isAktif,
        public readonly bool $mustReset,
        public readonly ?string $lastLoginAt = null,
    ) {
    }
}
