<?php

declare(strict_types=1);

namespace App\Models\Entities;

final class Orang
{
    public function __construct(
        public readonly int $id,
        public readonly string $nama,
        public readonly ?string $noHp,
        public readonly ?string $email,
        public readonly ?string $alamat,
        public readonly bool $isAktif,
        public readonly ?string $createdAt = null,
    ) {
    }
}
