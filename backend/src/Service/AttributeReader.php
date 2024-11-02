<?php

namespace App\Service;

use App\Attribute\Roles;
use ReflectionClass;
use ReflectionProperty;

class AttributeReader
{
    public function getDisallowedProperties(string $objectClassName, array $roles = []): array
    {
        $disallowedProperties = [];

        if (empty($roles)) {
            return $disallowedProperties;
        }

        foreach ($this->getProperties($objectClassName) as $property) {
            foreach ($property->getAttributes(Roles::class) as $attribute) {
                /** @var Roles $instance */
                $instance = $attribute->newInstance();
                if (!array_intersect($roles, $instance->roles)) {
                    $disallowedProperties[] = $property->getName();
                }
            }
        }

        return array_unique($disallowedProperties);
    }

    /** @return ReflectionProperty[] */
    private function getProperties(string $objectClassName): array
    {
        return (new ReflectionClass($objectClassName))->getProperties();
    }
}