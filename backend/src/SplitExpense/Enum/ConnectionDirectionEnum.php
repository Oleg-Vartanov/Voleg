<?php

namespace App\SplitExpense\Enum;

enum ConnectionDirectionEnum: string
{
    case INCOMING = 'incoming';
    case OUTGOING = 'outgoing';
}
