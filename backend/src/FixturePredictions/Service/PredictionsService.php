<?php

namespace App\FixturePredictions\Service;

use App\FixturePredictions\DTO\Request\PredictionDto;
use App\FixturePredictions\Entity\Fixture;
use App\FixturePredictions\Entity\FixturePrediction;
use App\FixturePredictions\Exception\FixtureHasStartedException;
use App\FixturePredictions\Messenger\CalculatePointsMessage;
use App\FixturePredictions\Repository\FixturePredictionRepository;
use App\FixturePredictions\Repository\FixtureRepository;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class PredictionsService
{
    public function __construct(
        private FixtureRepository $fixtureRepository,
        private FixturePredictionRepository $predictionRepository,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function dispatchUpdatePoints(Fixture $fixture): void
    {
        if ($fixture->canCalculatePoints()) {
            $this->messageBus->dispatch(new CalculatePointsMessage($fixture->getId()));
        }
    }

    public function updatePoints(Fixture $fixture): void
    {
        if ($fixture->canCalculatePoints()) {
            foreach ($fixture->getFixturePredictions() as $prediction) {
                $points = $this->calculatePoints($prediction);
                $prediction->setPoints($points);
                $this->entityManager->persist($prediction);
            }
        }
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
