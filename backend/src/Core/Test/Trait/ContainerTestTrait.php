<?php

namespace App\Core\Test\Trait;

trait ContainerTestTrait
{
    /**
     * Gets typehinted service.
     *
     * @template T
     * @param class-string<T> $id
     * @return T
     */
    protected function getService(string $id): mixed
    {
        return static::getContainer()->get($id);
    }
}
