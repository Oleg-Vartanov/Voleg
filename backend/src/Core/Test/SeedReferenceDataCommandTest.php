<?php

namespace App\Core\Test;

use App\Core\Command\SeedReferenceDataCommand;
use App\Core\Repository\CountryRepository;
use App\Core\Repository\CurrencyRepository;
use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use App\SplitExpense\Repository\SeCategoryRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SeedReferenceDataCommandTest extends WebTestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        static::bootKernel();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->connection = $entityManager->getConnection();
        $this->connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        try {
            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }
        } finally {
            parent::tearDown();
        }
    }

    public function testItSynchronizesReferenceDataIdempotently(): void
    {
        $container = static::getContainer();
        $command = $container->get(SeedReferenceDataCommand::class);

        $firstRun = new CommandTester($command);
        self::assertSame(0, $firstRun->execute([]));
        self::assertStringContainsString('Reference data synchronized', $firstRun->getDisplay());

        $secondRun = new CommandTester($command);
        self::assertSame(0, $secondRun->execute([]));
        self::assertStringContainsString('Reference data synchronized', $secondRun->getDisplay());

        self::assertCount(253, $container->get(CountryRepository::class)->findAll());
        $usd = $container->get(CurrencyRepository::class)->findOneBy(['code' => 'USD']);
        self::assertSame('US Dollar', $usd?->getName());
        self::assertSame(2, $usd?->getDecimalPlaces());
        self::assertCount(109, $container->get(SeasonRepository::class)->findAll());
        self::assertCount(1, $container->get(CompetitionRepository::class)->findAll());
        self::assertNotNull($container->get(SeCategoryRepository::class)->findOneBy(['tag' => 'groceries']));
        self::assertNotNull($container->get(SeCategoryRepository::class)->findOneBy(['tag' => 'entertainment']));
    }
}
