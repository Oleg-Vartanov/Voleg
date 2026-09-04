<?php

namespace App\FixturePredictions\DataFixture;

use App\FixturePredictions\Entity\FixturePrediction;
use App\FixturePredictions\Repository\FixtureRepository;
use App\FixturePredictions\Service\PredictionsService;
use App\User\DataFixture\UserFixture;
use App\User\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FixturePredictionFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly FixtureRepository $fixtureRepository,
        private readonly PredictionsService $predictionsService,
    ) {
    }

    /**
     * @return class-string[]
     */
    public function getDependencies(): array
    {
        return [
            UserFixture::class,
            FixtureFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $users = $this->userRepository->list();
        $fixtures = $this->fixtureRepository->findBy([], limit: 20);

        foreach ($users as $user) {
            foreach ($fixtures as $fixture) {
                $actualHomeScore = $fixture->getHomeScore();
                $actualAwayScore = $fixture->getAwayScore();
                if ($actualHomeScore === null || $actualAwayScore === null) {
                    continue;
                }

                // Randomize prediction results.
                [$homeScore, $awayScore] = rand(0, 1)
                    ? [$actualHomeScore, $actualAwayScore]
                    : [1, 1];

                $fixturePrediction = new FixturePrediction();
                $fixturePrediction->setUser($user);
                $fixturePrediction->setFixture($fixture);
                $fixturePrediction->setHomeScore($homeScore);
                $fixturePrediction->setAwayScore($awayScore);
                $fixturePrediction->setPoints(
                    $this->predictionsService->calculatePoints($fixturePrediction)
                );

                $manager->persist($fixturePrediction);
            }
        }

        $manager->flush();
    }
}
