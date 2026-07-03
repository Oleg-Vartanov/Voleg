<?php

namespace App\Core\Enum;

use App\Core\Trait\EnumExtender;

/**
 * Serialization/Validation group.
 */
enum Group: string
{
    use EnumExtender;

    case default = 'Default';

    /** Access */
    case public = 'public';
    case admin = 'admin';
    case owner = 'owner';

    /** Action */
    case read = 'read';
    case update = 'update';
    case create = 'create';
}
