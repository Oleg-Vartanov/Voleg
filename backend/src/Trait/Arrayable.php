<?php

namespace App\Trait;

trait Arrayable
{
    public static function createByArray(array $values): self
    {
        return (new self())->setByArray($values);
    }

    public function setByArray(array $values): self
    {
        foreach ($values as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}