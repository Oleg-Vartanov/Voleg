<?php

namespace App\Core\Trait;

trait EnumExtender
{
    /**
     * @return mixed[]
     */
    public static function values(): array
    {
        return array_map(function ($case) {
            return $case->value;
        }, self::cases());
    }

    /**
     * @return mixed[]
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->value;
        }

        return $options;
    }
}
