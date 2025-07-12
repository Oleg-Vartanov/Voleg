<?php

namespace App\FixturePredictions\Controller;

use App\Core\DTO\Documentation\Validator\ValidationErrorResponse;
use App\FixturePredictions\DTO\Request\FixturesDto;
use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\FixturePredictionRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use App\User\Entity\User;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Fixtures')]
#[Security(name: 'Bearer')]
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(
            property: 'users',
            properties: [
                new OA\Property(property: 'user', ref: new Model(type: User::class, groups: [User::SHOW])),
                new OA\Property(property: 'totalPoints', type: 'int'),
                new OA\Property(property: 'periodPoints', type: 'int'),
            ]
        ),
    ]),
)]
#[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized')]
#[OA\Response(
    response: Response::HTTP_UNPROCESSABLE_ENTITY,
    description: 'Validation errors',
    content: new Model(type: ValidationErrorResponse::class)
)]

#[Route(
    path: '/fixtures/leaderboard',
    name: 'fixtures_leaderboard',
    methods: [Request::METHOD_GET],
    format: 'json'
)]
class LeaderboardGetAction extends AbstractController
{
    public function __construct(
        private readonly CompetitionRepository $competitionRepository,
        private readonly SeasonRepository $seasonRepository,
        private readonly FixturePredictionRepository $fpRepository,
    ) {
    }

    public function __invoke(
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        FixturesDto $dto = new FixturesDto(),
    ): JsonResponse {
        $competition = $this->competitionRepository->findOneByCode($dto->competitionCode);

        $users = $this->fpRepository->leaderboard(
            competition: $competition,
            season: $this->seasonRepository->findOneByYear($dto->year),
            start: $dto->start,
            end: $dto->end,
            limit: $dto->limit ?? 50,
        );

        return $this->json([
            'filters' => [
                'start' => $dto->start->format('Y-m-d'),
                'end' => $dto->end->format('Y-m-d'),
                'competition' => $competition?->getCode(),
            ],
            'users' => $users,
        ], context: ['groups' => [User::SHOW]]);
    }
}