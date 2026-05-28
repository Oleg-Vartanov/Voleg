<?php

namespace App\Core\Enum;

/**
 * Serializer groups for API access.
 */
class Group
{
    public const string PUBLIC = 'public';
    public const string ADMIN = 'admin';
    public const string OWNER = 'owner';

    /** @var string[] */
    public const array ALL = [self::PUBLIC, self::ADMIN, self::OWNER];
}