<?php

namespace App\Core\Trait;

trait PropertyAccessor
{
    /**
     * For this method to work properties should have a type. Even mixed will do.
     * Otherwise, get_object_vars will return property with value null.
     */
    public function isPropertyInitialized(string $propertyName): bool
    {
        return array_key_exists($propertyName, get_object_vars($this));
    }
}