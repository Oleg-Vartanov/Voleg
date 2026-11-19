<?php

namespace App\FixturePredictions\Enum;

use App\Core\Enum\EnumExtender;

enum CompetitionCodeEnum: string
{
    use EnumExtender;

    case EPL = 'PL';
}
