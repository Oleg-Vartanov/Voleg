<?php

namespace App\Enum\Fixtures;

use App\Trait\EnumExtender;

enum CompetitionCodeEnum: string
{
    use EnumExtender;
    
    case EPL = 'PL';
}