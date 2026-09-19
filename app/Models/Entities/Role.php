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
    case PENGAWAS_OLAH = 'PENGAWAS_OLAH';
    case SM_SOSIAL = 'SM_SOSIAL';
    case SM_PLS = 'SM_PLS';
    case VIEWER = 'VIEWER';
}
