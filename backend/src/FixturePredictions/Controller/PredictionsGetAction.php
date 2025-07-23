<?php

namespace App\FixturePredictions\Controller;

use App\Core\DTO\Documentation\Validator\ValidationErrorResponse;
use App\FixturePredictions\DTO\Request\FixturesDto;
use App\FixturePredictions\Entity\Fixture;
use App\FixturePredictions\Entity\FixturePrediction;
use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\FixtureRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
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
            property: 'filters',
            properties: [
                new OA\Property(property: 'start', description: 'Y-m-d', type: 'string'),
                new OA\Property(property: 'end', description: 'Y-m-d', type: 'string'),
                new OA\Property(property: 'competition', type: 'string'),
                new OA\Property(property: 'season', type: 'int'),
                new OA\Property(property: 'limit', type: 'int'),
            ]
        ),
        new OA\Property(property: 'fixtures', ref: new Model(type: Fixture::class)),
    ]),
)]
#[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized')]
#[OA\Response(
    response: Response::HTTP_UNPROCESSABLE_ENTITY,
    description: 'Validation errors',
    content: new Model(type: ValidationErrorResponse::class)
)]

#[Route(
    path: '/fixtures/predictions',
    name: 'fixtures_predictions',
    methods: [Request::METHOD_GET],
    format: 'json'
)]
class PredictionsGetAction extends AbstractController
{
    public function __construct(
        private readonly CompetitionRepository $competitionRepository,
        private readonly FixtureRepository $fixtureRepository,
        private readonly SeasonRepository $seasonRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        FixturesDto $dto = new FixturesDto(),
    ): JsonResponse {
        $users = empty($dto->userIds) ? [] : $this->userRepository->findBy(['id' => $dto->userIds]);
        array_unshift($users, $this->getUser());

        $competition = $this->competitionRepository->findOneByCode($dto->competitionCode);

        $fixtures = $this->fixtureRepository->filter(
            users: $users,
            competition: $competition,
            season: $this->seasonRepository->findOneByYear($dto->season),
            start: $dto->start,
            end: $dto->end,
            limit: $dto->limit,
        );

        return $this->json([
            'filters' => [
                'start' => $dto->start->format('Y-m-d'),
                'end' => $dto->end->format('Y-m-d'),
                'competition' => $competition?->getCode(),
                'season' => $dto->season,
                'limit' => $dto->limit,
            ],
            'fixtures' => $fixtures,
        ], context: ['groups' => [FixturePrediction::SHOW_PREDICTIONS, User::SHOW]]);
    }
}
