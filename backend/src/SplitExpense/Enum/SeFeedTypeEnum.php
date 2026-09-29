<?php

namespace App\SplitExpense\Enum;

use App\Core\Trait\EnumExtender;

enum SeFeedTypeEnum: string
{
    use EnumExtender;

    case EXPENSE = 'expense';
    case ADJUSTMENT = 'adjustment';
}
