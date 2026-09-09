<?php

declare(strict_types=1);

return [
    'name' => $_ENV['APP_NAME'] ?? 'SIPANDHALU',
    'full_name' => 'Sistem Informasi Pandhalungan Susenas-Seruti',
    'tagline' => 'Siji Data, Akeh Dulur — Ganti Triwulan, Ganti NKS, Tetap Siji Data',
    'env' => $_ENV['APP_ENV'] ?? 'development',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'key' => $_ENV['APP_KEY'] ?? '',
];
