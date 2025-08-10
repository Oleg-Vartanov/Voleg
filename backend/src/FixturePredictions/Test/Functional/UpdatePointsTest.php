<?php

namespace App\FixturePredictions\Test\Functional;

use App\Core\Test\Trait\ContainerTestTrait;
use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Fixture;
use App\FixturePredictions\Entity\FixturePrediction;
use App\FixturePredictions\Entity\Season;
use App\FixturePredictions\Entity\Team;
use App\FixturePredictions\Repository\FixturePredictionRepository;
use App\FixturePredictions\Service\PredictionsService;
use App\User\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

#[TestDox('Fixture Predictions')]
class UpdatePointsTest extends KernelTestCase
{
    use ContainerTestTrait;

    private EntityManagerInterface $em;
    private FixturePredictionRepository $predictionRepository;
    private PredictionsService $predictionsService;

    public function setUp(): void
    {
        $this->predictionsService = $this->getService(PredictionsService::class);
        $this->em = $this->getService(EntityManagerInterface::class);
        $this->predictionRepository = $this->getService(FixturePredictionRepository::class);
    }

    /**
     * @throws ExceptionInterface
     * @throws ORMException
     */
    #[TestDox('Update points')]
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

        $predictions = $this->predictionRepository->findByFixture($fixture);

        self::assertCount(4, $predictions);
        if (count($predictions) === 4) {
            self::assertSame(3, $predictions[0]->getPoints());
            self::assertSame(1, $predictions[1]->getPoints());
            self::assertSame(0, $predictions[2]->getPoints());
            self::assertSame(0, $predictions[3]->getPoints());
        }
    }

    /**
     * @throws ORMException
     */
    private function prepareFixture(int $scoreHome, int $scoreAway): Fixture
    {
        $f = new Fixture();
        $f->setHomeScore($scoreHome);
        $f->setAwayScore($scoreAway);
        $f->setStartAt(new DateTimeImmutable('-1 day'));

        $f->setMatchday(1);
        $f->setCompetition($this->em->getReference(Competition::class, 1));
        $f->setSeason($this->em->getReference(Season::class, 1));
        $f->setHomeTeam($this->em->getReference(Team::class, 1));
        $f->setAwayTeam($this->em->getReference(Team::class, 2));

        $this->em->persist($f);

        return $f;
    }

    /**
     * @throws ORMException
     */
    private function preparePrediction(
        Fixture $fixture,
        int $scoreHome,
        int $scoreAway
    ): FixturePrediction {
        $p = new FixturePrediction();
        $p->setFixture($fixture);
        $p->setHomeScore($scoreHome);
        $p->setAwayScore($scoreAway);

        $p->setUser($this->em->getReference(User::class, 1));

        $this->em->persist($p);

        return $p;
    }
}
