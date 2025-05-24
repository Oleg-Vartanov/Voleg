<?php

namespace App\FixturePredictions\Enum;

use App\Core\Trait\EnumExtender;

enum CompetitionCodeEnum: string
{
    use EnumExtender;
    
    case EPL = 'PL';
}