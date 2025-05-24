<?php

namespace App\Core\Helper;

class Arr
{
    public static function castItemsToIntIfPossible(array $array): array
    {
        return array_map(function ($item) {
            // Check if the item can be safely cast to an integer.
            if (is_numeric($item) && (int)$item == $item) {
                return (int)$item;
            }
            // Return the item as-is if it can't be safely cast.
            return $item;
        }, $array);
    }
}