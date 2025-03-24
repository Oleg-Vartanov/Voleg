<?php

namespace App\Enum\Fixtures;

use App\Trait\EnumExtender;

enum FixtureStatusEnum: string
{
    use EnumExtender;

    case Scheduled = 'scheduled';
    case Finished = 'finished';
    case InPlay = 'in-play';
    case Unknown = 'unknown';
}