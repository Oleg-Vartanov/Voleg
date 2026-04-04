<?php

namespace App\Core\Test\Unit;

use App\Core\Trait\EnumExtender;

enum TestEnum: string
{
    use EnumExtender;

    case FIRST = 'first';
    case SECOND = 'second';
    case THIRD = 'third';
}
