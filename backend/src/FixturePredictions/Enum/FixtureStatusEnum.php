<?php

namespace App\FixturePredictions\Enum;

use App\Core\Trait\EnumExtender;

enum FixtureStatusEnum: string
{
    use EnumExtender;

    case Scheduled = 'scheduled';
    case Finished = 'finished';
    case InPlay = 'in-play';
    case Unknown = 'unknown';
}