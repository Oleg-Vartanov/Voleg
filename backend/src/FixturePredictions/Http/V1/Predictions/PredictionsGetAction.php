<?php

namespace App\FixturePredictions\Http\V1\Predictions;

use App\Core\Documentation\Attribute\Response\UnauthorizedResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\Core\Http\ApiController;
use App\FixturePredictions\Entity\Fixture;
use App\FixturePredictions\Entity\FixturePrediction;
use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\FixtureRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Get(
    security: [['Bearer' => []]],
    tags: ['Fixtures'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'List of fixtures',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'filters', ref: new Model(type: PredictionsFiltersResponse::class)),
                new OA\Property(property: 'fixtures', ref: new Model(type: Fixture::class)),
            ]),
        ),
        new UnauthorizedResponse(),
        new ValidationErrorResponse(),
    ],
)]
#[Route(
    path: '/fixtures/predictions',
    name: 'fixtures_predictions',
    methods: [Request::METHOD_GET],
    format: 'json'
)]
class PredictionsGetAction extends ApiController
{
    public function __construct(
        private readonly CompetitionRepository $competitionRepository,
        private readonly FixtureRepository $fixtureRepository,
        private readonly SeasonRepository $seasonRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(
        #[CurrentUser] User $user,
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        PredictionsRequest $dto = new PredictionsRequest(),
    ): JsonResponse {
        /** @var array<User> $users */
        $users = empty($dto->userIds) ? [] : $this->userRepository->findBy(['id' => $dto->userIds]);
        array_unshift($users, $user);

        $competition = $this->competitionRepository->findOneByCode($dto->competitionCode);
        $season = $this->seasonRepository->findByYearOrCompetition(
            $dto->season,
            $competition,
            $dto->defaultToCurrentSeason
        );

        $filters = new PredictionsFiltersResponse(
            start: $dto->start,
            end: $dto->end,
            competitionEntity: $competition,
            seasonEntity: $season,
            limit: $dto->limit,
        );
        $fixtures = $this->fixtureRepository->filter(
            users: $users,
            competition: $competition,
            season: $season,
            start: $dto->start,
            end: $dto->end,
            limit: $dto->limit,
        );

        return $this->json(
            data: [
                'filters' => $filters,
                'fixtures' => $fixtures,
            ],
            context: [
                'groups' => [
                    FixturePrediction::SHOW_PREDICTIONS,
                    User::SHOW,
                    PredictionsFiltersResponse::GROUP,
                ],
            ],
        );
    }
}
