<?php

namespace App\Core\Test\Unit;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('EnumExtender')]
class EnumExtenderTest extends TestCase
{
    public function testValues(): void
    {
        $expected = ['first', 'second', 'third'];
        $this->assertSame($expected, TestEnum::values());
    }

    public function testOptions(): void
    {
        $expected = [
            'first' => 'first',
            'second' => 'second',
            'third' => 'third',
        ];
        $this->assertSame($expected, TestEnum::options());
    }
}
