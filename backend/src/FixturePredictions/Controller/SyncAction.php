<?php

namespace App\FixturePredictions\Controller;

use App\Core\DTO\Documentation\Validator\ValidationErrorResponse;
use App\FixturePredictions\DTO\Request\SyncDto;
use App\FixturePredictions\Interface\FixturesProviderInterface;
use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use Nelmio\ApiDocBundle\Attribute\Model;
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
#[OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Dont have rights')]
#[OA\Response(
    response: Response::HTTP_UNPROCESSABLE_ENTITY,
    description: 'Validation errors',
    content: new Model(type: ValidationErrorResponse::class)
)]

#[Route(
    path: '/fixtures/sync',
    name: 'fixtures_sync',
    methods: [Request::METHOD_POST]
)]
class SyncAction extends AbstractController
{
    public function __construct(
        private readonly CompetitionRepository $competitionRepository,
        private readonly FixturesProviderInterface $fixturesProvider,
        private readonly SeasonRepository $seasonRepository,
    ) {
    }

    public function __invoke(#[MapRequestPayload] SyncDto $dto): Response
    {
        $competition = $this->competitionRepository->findOneByCode($dto->competitionCode)
            ?? throw new NotFoundHttpException();
        $season = $this->seasonRepository->findOneByYear($dto->seasonYear)
            ?? throw new NotFoundHttpException();

        $this->fixturesProvider->syncTeams($competition, $season);
        $this->fixturesProvider->syncFixtures($competition, $season);

        return new Response('Synced');
    }
}