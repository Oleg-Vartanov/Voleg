<?php

namespace App\Core\Test\Unit;

use App\Core\Util\MoneyUtil;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('MoneyUtil')]
class MoneyUtilTest extends TestCase
{
    #[TestDox('toMinorUnits: USD-style amounts')]
    public function testToMinorUnits(): void
    {
        self::assertSame(10000, MoneyUtil::toMinorUnits('100', 2));
        self::assertSame(10050, MoneyUtil::toMinorUnits('100.50', 2));
        self::assertSame(1, MoneyUtil::toMinorUnits('0.01', 2));
    }

    #[TestDox('toMinorUnits: zero-decimal currency')]
    public function testToMinorUnitsZeroDecimals(): void
    {
        self::assertSame(500, MoneyUtil::toMinorUnits('500', 0));
    }

    #[TestDox('toMinorUnits: rejects excess precision')]
    public function testToMinorUnitsRejectsExcessPrecision(): void
    {
        self::expectException(InvalidArgumentException::class);
        MoneyUtil::toMinorUnits('1.234', 2);
    }

    #[TestDox('fromMinorUnits: formats with decimal places')]
    public function testFromMinorUnits(): void
    {
        self::assertSame('100.50', MoneyUtil::fromMinorUnits(10050, 2));
        self::assertSame('500', MoneyUtil::fromMinorUnits(500, 0));
    }
}
