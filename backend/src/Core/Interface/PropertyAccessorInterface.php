<?php

namespace App\Core\Interface;

interface PropertyAccessorInterface
{
    public function isPropertyInitialized(string $propertyName): bool;
}