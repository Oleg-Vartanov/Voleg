<?php

namespace App\FixturePredictions\Test\Functional;

use App\Core\Test\Trait\ContainerTestTrait;
use App\FixturePredictions\Entity\Fixture;
use App\FixturePredictions\Entity\FixturePrediction;
use App\FixturePredictions\Repository\FixturePredictionRepository;
use App\FixturePredictions\Service\PredictionsService;
use App\FixturePredictions\Test\Trait\FootballFixtureTestTrait;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use App\User\Service\UserService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

#[TestDox('Fixture Predictions')]
class UpdatePointsTest extends KernelTestCase
{
    use ContainerTestTrait;
    use FootballFixtureTestTrait;

    private EntityManagerInterface $em;
    private PredictionsService $predictionsService;

    public function setUp(): void
    {
        self::bootKernel();
        $this->em = $this->getService(EntityManagerInterface::class);
        $this->predictionsService = $this->getService(PredictionsService::class);
    }

    /**
     * @throws ExceptionInterface
     */
    #[TestDox('Update points: dispatch success')]
    public function testDispatchUpdatePoints(): void
    {
        $fixture = $this->prepareFixture(1, 2);
        $this->preparePrediction($fixture, 1, 2);
        $this->preparePrediction($fixture, 1, 3);
        $this->preparePrediction($fixture, 1, 1);
        $this->preparePrediction($fixture, 1, 0);

        $this->em->flush();
        $this->em->clear(); // To reset dispatcher em.

        $this->predictionsService->dispatchUpdatePoints($fixture);

        $predictions = $this->getService(FixturePredictionRepository::class)
            ->findByFixture($fixture);

        self::assertCount(4, $predictions);
        if (count($predictions) === 4) {
            self::assertSame(3, $predictions[0]->getPoints());
            self::assertSame(1, $predictions[1]->getPoints());
            self::assertSame(0, $predictions[2]->getPoints());
            self::assertSame(0, $predictions[3]->getPoints());
        }
    }

    /**
     * @throws ExceptionInterface
     */
    #[TestDox('Update points: handle non existent fixture')]
    public function testHandleNonExistentFixture(): void
    {
        $fixture = self::createStub(Fixture::class);
        $fixture->method('getId')->willReturn(0);
        $fixture->method('canCalculatePoints')->willReturn(true);

        $this->predictionsService->dispatchUpdatePoints($fixture);
        self::assertTrue(true);
    }

    private function prepareFixture(int $scoreHome, int $scoreAway): Fixture
    {
        $fixture = $this->createFootballFixture(
            new DateTimeImmutable('-1 day'),
            300001,
        );
        $fixture->setHomeScore($scoreHome);
        $fixture->setAwayScore($scoreAway);
        $this->em->flush();

        return $fixture;
    }

    private function preparePrediction(
        Fixture $fixture,
        int $scoreHome,
        int $scoreAway,
    ): FixturePrediction {
        $user = $this->createTestUser();

        $prediction = new FixturePrediction();
        $prediction->setFixture($fixture);
        $prediction->setHomeScore($scoreHome);
        $prediction->setAwayScore($scoreAway);
        $prediction->setUser($user);

        $this->em->persist($prediction);

        return $prediction;
    }

    private function createTestUser(): User
    {
        $index = bin2hex(random_bytes(6));
        $user = $this->getService(UserService::class)->create(
            "predictions-{$index}@example.com",
            'Qwerty1!',
            "predictions-{$index}",
        );
        $user->setVerified(true);
        $this->getService(UserRepository::class)->save($user, true);

        return $user;
    }
}
