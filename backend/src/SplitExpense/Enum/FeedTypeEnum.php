<?php

namespace App\SplitExpense\Enum;

use App\Core\Trait\EnumExtender;

enum FeedTypeEnum: string
{
    use EnumExtender;

    case EXPENSE = 'expense';
    case ADJUSTMENT = 'adjustment';
}
