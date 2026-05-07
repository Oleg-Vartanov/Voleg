<?php

namespace App\User\Enum;

enum UserTokenTypeEnum: string
{
    case VERIFICATION = 'VERIFICATION';
    case EMAIL_CHANGE = 'EMAIL_CHANGE';
    case PASSWORD_RESET = 'PASSWORD_RESET';
}
