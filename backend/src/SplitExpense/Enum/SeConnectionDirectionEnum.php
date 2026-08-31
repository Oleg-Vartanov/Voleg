<?php

namespace App\SplitExpense\Enum;

enum SeConnectionDirectionEnum: string
{
    case INCOMING = 'incoming';
    case OUTGOING = 'outgoing';
}
