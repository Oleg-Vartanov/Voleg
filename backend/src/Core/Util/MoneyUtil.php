<?php

namespace App\Core\Util;

use InvalidArgumentException;

final class MoneyUtil
{
    public static function minorUnitFactor(int $decimalPlaces): int
    {
        return 10 ** $decimalPlaces;
    }

    /**
     * @param numeric-string $amount
     */
    public static function toMinorUnits(string $amount, int $decimalPlaces): int
    {
        if (!preg_match('/^\d+(\.\d+)?$/', $amount)) {
            throw new InvalidArgumentException('Amount must be a non-negative decimal number.');
        }

        if (preg_match('/\.(\d+)/', $amount, $matches) && strlen($matches[1]) > $decimalPlaces) {
            throw new InvalidArgumentException(
                sprintf('Amount has more than %d decimal places.', $decimalPlaces),
            );
        }

        return (int) bcmul($amount, (string) self::minorUnitFactor($decimalPlaces), 0);
    }

    public static function fromMinorUnits(int $minorUnits, int $decimalPlaces): string
    {
        if ($decimalPlaces === 0) {
            return (string) $minorUnits;
        }

        return bcdiv((string) $minorUnits, (string) self::minorUnitFactor($decimalPlaces), $decimalPlaces);
    }
}
