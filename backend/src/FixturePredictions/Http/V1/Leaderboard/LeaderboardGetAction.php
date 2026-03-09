<?php

namespace App\FixturePredictions\Http\V1\Leaderboard;

use App\Core\Documentation\Attribute\Response\UnauthorizedResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\Core\Http\ApiController;
use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\FixturePredictionRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use App\User\Entity\User;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Get(
    security: [['Bearer' => []]],
    tags: ['Fixtures'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'OK',
            content: new OA\JsonContent(properties: [
                new OA\Property(
                    property: 'filters',
                    ref: new Model(type: LeaderboardFiltersResponse::class)
                ),
                new OA\Property(
                    property: 'users',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: LeaderboardRow::class, groups: User::SHOW_ALL))
                ),
            ]),
        ),
        new UnauthorizedResponse(),
        new ValidationErrorResponse(),
    ],
)]
#[Route(
    path: '/fixtures/leaderboard',
    name: 'fixtures_leaderboard',
    methods: [Request::METHOD_GET],
    format: 'json'
)]
class LeaderboardGetAction extends ApiController
{
    public const string GROUP = 'leaderboard';

    public function __construct(
        private readonly CompetitionRepository $competitionRepository,
        private readonly SeasonRepository $seasonRepository,
        private readonly FixturePredictionRepository $fpRepository,
    ) {
    }

    public function __invoke(
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        LeaderboardRequest $dto = new LeaderboardRequest(),
    ): JsonResponse {
        $competition = $this->competitionRepository->findOneByCode($dto->competitionCode);
        $season = $this->seasonRepository->findByYearOrCompetition(
            $dto->season,
            $competition,
            $dto->defaultToCurrentSeason
        );

        $filters = new LeaderboardFiltersResponse(
            start: $dto->start,
            end: $dto->end,
            competitionEntity: $competition,
            seasonEntity: $season,
            limit: $dto->limit,
        );
        $leaderboard = $this->fpRepository->leaderboard(
            competition: $competition,
            season: $season,
            start: $dto->start,
            end: $dto->end,
            limit: $dto->limit,
        );

        return $this->json([
            'filters' => $filters,
            'users' => $leaderboard,
        ], context: ['groups' => [User::SHOW, self::GROUP]]);
    }
}
