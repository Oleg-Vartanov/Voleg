<?php

namespace App\SplitExpense\Enum;

enum SeConnectionStatusEnum: string
{
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case PENDING = 'pending';
    case BLOCKED = 'blocked';
}
