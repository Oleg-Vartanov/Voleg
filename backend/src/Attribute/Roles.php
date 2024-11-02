<?php

namespace App\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Roles
{
    /**
     * @param string[] $roles User roles.
     */
    public function __construct(public array $roles = [])
    {
    }
}