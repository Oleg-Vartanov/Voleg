<?php

namespace App\User\Enum;

enum RoleEnum: string
{
    case ROLE_USER = 'ROLE_USER';
    case ROLE_ADMIN = 'ROLE_ADMIN';

    /** Temporary role. Depends on context. */
    case OWNER = 'OWNER';
}
