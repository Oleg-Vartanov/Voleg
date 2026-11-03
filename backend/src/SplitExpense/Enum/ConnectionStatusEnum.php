<?php

namespace App\SplitExpense\Enum;

enum ConnectionStatusEnum: string
{
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case PENDING = 'pending';
}
