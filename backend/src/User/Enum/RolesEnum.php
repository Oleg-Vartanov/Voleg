<?php

namespace App\User\Enum;

enum RolesEnum: string
{
    case ROLE_USER = 'ROLE_USER';
    case ROLE_ADMIN = 'ROLE_ADMIN';
    case OWNER = 'OWNER'; // Temporary role. Depends on context.
}