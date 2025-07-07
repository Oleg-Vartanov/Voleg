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

        $this->assertSame(0, $exitCode);

        $display = $commandTester->getDisplay();

        $this->assertStringContainsString('Populated country table', $display);
        $this->assertStringContainsString('Populated competition table', $display);
        $this->assertStringContainsString('Populated season table', $display);
    }
}
