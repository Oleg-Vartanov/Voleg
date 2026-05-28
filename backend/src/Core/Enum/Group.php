<?php

namespace App\Core\Enum;

use App\Core\Trait\EnumExtender;

/**
 * Serialization group.
 */
enum Group: string
{
    use EnumExtender;

    /** Access */
    case public = 'public';
    case admin = 'admin';
    case owner = 'owner';

    /** Action */
    case read = 'read';
    case update = 'update';
    case create = 'create';
}
