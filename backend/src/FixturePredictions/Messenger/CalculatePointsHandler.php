<?php

namespace App\FixturePredictions\Messenger;

use App\FixturePredictions\Repository\FixtureRepository;
use App\FixturePredictions\Service\PredictionsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CalculatePointsHandler
{
    public function __construct(
        private FixtureRepository $fixtureRepository,
        private PredictionsService $predictionsService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(CalculatePointsMessage $message): void
    {
        $fixture = $this->fixtureRepository->findOneById($message->getFixtureId());

        if (null === $fixture) {
            return;
        }

        $this->predictionsService->updatePoints($fixture);
        $this->entityManager->flush();
    }
}
