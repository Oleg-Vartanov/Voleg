<?php

namespace App\FixturePredictions\Http\V1\Sync;

use App\Core\Documentation\Attribute\Response\AccessDeniedResponse;
use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\UnauthorizedResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\Core\Http\ApiController;
use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use App\FixturePredictions\Service\FixtureProvider;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Post(
    security: [['Bearer' => []]],
    tags: ['Fixtures'],
    responses: [
        new MessageResponse(description: 'Synced'),
        new UnauthorizedResponse(),
        new AccessDeniedResponse(),
        new ValidationErrorResponse(),
    ],
)]
#[Route(
    path: '/fixtures/sync',
    name: 'fixtures_sync',
    methods: [Request::METHOD_POST]
)]
class SyncAction extends ApiController
{
    public function __construct(
        private readonly CompetitionRepository $competitionRepository,
        private readonly FixtureProvider $fixturesProvider,
        private readonly SeasonRepository $seasonRepository,
    ) {
    }

    public function __invoke(#[MapRequestPayload] SyncRequest $dto): Response
    {
        $competition = $this->competitionRepository->findOneByCode($dto->competitionCode)
            ?? throw new NotFoundHttpException();
        $season = $this->seasonRepository->findOneByYear($dto->seasonYear)
            ?? throw new NotFoundHttpException();

        $this->fixturesProvider->sync($competition, $season, $dto->from, $dto->to);

        return $this->messageResponse('Synced');
    }
}
