<?php

namespace App\Core\Test;

use App\Core\Command\PopulateDbCommand;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[TestDox('Core')]
class PopulateDbCommandTest extends WebTestCase
{
    #[TestDox('Execute populate data command')]
    public function testExecutePopulateData(): void
    {
        $container = static::getContainer();
        $command = $container->get(PopulateDbCommand::class);

        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute([]);

        self::assertSame(0, $exitCode);

        $display = $commandTester->getDisplay();

        self::assertStringContainsString('Populated country table', $display);
        self::assertStringContainsString('Populated competition table', $display);
        self::assertStringContainsString('Populated season table', $display);
    }
}
