<?php

namespace App\FixturePredictions\Service;

use App\FixturePredictions\DTO\Request\PredictionDto;
use App\FixturePredictions\Entity\FixturePrediction;
use App\FixturePredictions\Exception\FixtureHasStartedException;
use App\FixturePredictions\Repository\FixturePredictionRepository;
use App\FixturePredictions\Repository\FixtureRepository;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PredictionsService
{
    public function __construct(
        private FixtureRepository $fixtureRepository,
        private FixturePredictionRepository $predictionRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function calculatePoints(FixturePrediction $prediction): int
    {
        $fixture = $prediction->getFixture();

        $homeScore = $fixture->getHomeScore();
        $awayScore = $fixture->getAwayScore();
        $pHomeScore = $prediction->getHomeScore();
        $pAwayScore = $prediction->getAwayScore();
        
        if ($homeScore === $pHomeScore && $awayScore === $pAwayScore) {
            return 3;
        }

        if (
            ($homeScore > $awayScore && $pHomeScore > $pAwayScore)
            || ($homeScore < $awayScore && $pHomeScore < $pAwayScore)
            || ($homeScore === $awayScore && $pHomeScore === $pAwayScore)
        ) {
            return 1;
        }

        return 0;
    }

    /** @param PredictionDto[] $dtos
     * @throws FixtureHasStartedException
     */
    public function makePredictions(array $dtos, User $user): void
    {
        foreach ($dtos as $dto) {
            $fixture = $this->fixtureRepository->findOneBy(['id' => $dto->fixtureId]);

            if ($fixture === null) {
                throw new NotFoundHttpException();
            }
            if ($fixture->hasStarted()) {
                throw new FixtureHasStartedException();
            }

            $prediction = $this->predictionRepository->findOneBy([
                'fixture' => $fixture,
                'user' => $user,
            ]);

            if ($prediction === null) {
                $prediction = new FixturePrediction();
                $prediction->setFixture($fixture);
                $prediction->setUser($user);
            }

            $prediction->setHomeScore($dto->homeScore);
            $prediction->setAwayScore($dto->awayScore);

            $this->entityManager->persist($prediction);
        }

        $this->entityManager->flush();
    }
}