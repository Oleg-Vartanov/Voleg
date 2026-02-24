<?php

namespace App\Core\Util;

use ReflectionClass;

class PropertyAccessor
{
    /**
     * @return string[] Property names.
     */
    public static function getInitializedProperties(object $object): array
    {
        $props = [];

        $ref = new ReflectionClass($object);

        foreach ($ref->getProperties() as $prop) {
            if (!$prop->isInitialized($object)) {
                continue;
            }

            $props[] = $prop->getName();
        }

        return $props;
    }
}
