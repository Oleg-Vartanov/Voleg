<?php

namespace App\Core\Test;

use App\Core\Command\SeedReferenceDataCommand;
use App\Core\Repository\CountryRepository;
use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SeedReferenceDataCommandTest extends WebTestCase
{
    public function testItSynchronizesReferenceDataIdempotently(): void
    {
        $container = static::getContainer();
        $command = $container->get(SeedReferenceDataCommand::class);

        $firstRun = new CommandTester($command);
        self::assertSame(0, $firstRun->execute([]));
        self::assertStringContainsString('Reference data synchronized', $firstRun->getDisplay());

        $secondRun = new CommandTester($command);
        self::assertSame(0, $secondRun->execute([]));
        self::assertStringContainsString('0 new seasons', $secondRun->getDisplay());

        self::assertCount(253, $container->get(CountryRepository::class)->findAll());
        self::assertCount(109, $container->get(SeasonRepository::class)->findAll());
        self::assertCount(1, $container->get(CompetitionRepository::class)->findAll());
    }
}
