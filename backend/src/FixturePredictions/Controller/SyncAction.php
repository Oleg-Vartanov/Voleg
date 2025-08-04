<?php

namespace App\FixturePredictions\Controller;

use App\Core\DTO\Documentation\Response as OACustomResponse;
use App\FixturePredictions\DTO\Request\SyncDto;
use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use App\FixturePredictions\Service\FixturesProvider;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Fixtures')]
#[Security(name: 'Bearer')]
#[OA\Response(response: Response::HTTP_OK, description: 'Fixtures synced')]
#[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized')]
#[OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access denied')]
#[OACustomResponse\ValidationErrorResponse]

#[Route(
    path: '/fixtures/sync',
    name: 'fixtures_sync',
    methods: [Request::METHOD_POST]
)]
class SyncAction extends AbstractController
{
    public function __construct(
        private readonly CompetitionRepository $competitionRepository,
        private readonly FixturesProvider $fixturesProvider,
        private readonly SeasonRepository $seasonRepository,
    ) {
    }

    public function __invoke(#[MapRequestPayload] SyncDto $dto): Response
    {
        $competition = $this->competitionRepository->findOneByCode($dto->competitionCode)
            ?? throw new NotFoundHttpException();
        $season = $this->seasonRepository->findOneByYear($dto->seasonYear)
            ?? throw new NotFoundHttpException();

        $this->fixturesProvider->sync($competition, $season);

        return new Response('Synced');
    }
}
