<?php

declare(strict_types=1);

namespace App\Models\Entities;

enum Role: string
{
    case ADMIN = 'ADMIN';
    case OPERATOR = 'OPERATOR';
    case PML = 'PML';
    case PCL = 'PCL';
    case PENGOLAH = 'PENGOLAH';
    case VIEWER = 'VIEWER';
}
