<?php

namespace App\Interface;

interface PropertyAccessorInterface
{
    public function isPropertyInitialized(string $propertyName): bool;
}