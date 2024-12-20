<?php

namespace App\Controller;

use App\DTO\Fixtures\Request\FixturesDto;
use App\DTO\Fixtures\Request\PredictionDto;
use App\DTO\Fixtures\Request\SyncDto;
use App\DTO\Validator\ValidationErrorResponse;
use App\Entity\User;
use App\Exception\FixtureHasStartedException;
use App\Interface\FixturesProviderInterface;
use App\Repository\CompetitionRepository;
use App\Repository\FixtureRepository;
use App\Repository\SeasonRepository;
use App\Repository\UserRepository;
use App\Service\Fixtures\PredictionsService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag(name: 'Fixtures')]
#[Route('/fixtures', name: 'fixtures_')]
class FixturesController extends ApiController
{
    const SHOW_PREDICTIONS = 'ShowPredictions';

    public function __construct(
        protected ValidatorInterface $validator,
        private readonly SerializerInterface $serializer,
        private readonly FixturesProviderInterface $fixturesProvider,
        private readonly CompetitionRepository $competitionRepository,
        private readonly SeasonRepository $seasonRepository,
        private readonly FixtureRepository $fixtureRepository,
        private readonly UserRepository $userRepository,
        private readonly PredictionsService $predictionsService,
    ) {
    }

    /* OpenAi Documentation */
    #[OA\RequestBody(content: new Model(type: SyncDto::class))]
    #[OA\Response(response: Response::HTTP_OK, description: 'Fixtures synced')]
    #[OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Dont have rights')]
    #[OA\Response(response: Response::HTTP_UNPROCESSABLE_ENTITY, description: 'Validation errors',
        content: new Model(type: ValidationErrorResponse::class))]

    #[Route('/sync', name: 'sync', methods: ['POST'])]
    public function sync(Request $request): Response {
        /** @var SyncDto $dto */
        $dto = $this->serializer->denormalize(
            $request->getPayload()->all(), SyncDto::class
        );

        if ($response = $this->validationErrorResponse($dto)) {
            return $response;
        }

        $competition = $this->competitionRepository->findOneByCode($dto->competitionCode) ?? throw new NotFoundHttpException();
        $season = $this->seasonRepository->findOneByYear($dto->seasonYear) ?? throw new NotFoundHttpException();

        $this->fixturesProvider->syncTeams($competition, $season);
        $this->fixturesProvider->syncFixtures($competition, $season);

        return new Response('Synced');
    }

    #[Route('/predictions', name: 'predictions', methods: ['GET'], format: 'json')]
    public function fixtures(
        #[MapQueryString(
            validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY
        )] FixturesDto $dto = new FixturesDto()
    ): JsonResponse {
        $dto->transform();

        $users = empty($dto->userIds) ? [] : $this->userRepository->findBy(['id' => $dto->userIds]);
        array_unshift($users, $this->getUser());

        $fixtures = $this->fixtureRepository->filter(
            users: $users,
            competition: $this->competitionRepository->findOneByCode($dto->competitionCode),
            season: $this->seasonRepository->findOneByYear($dto->year),
            start: $dto->start,
            end: $dto->end,
        );

        return $this->json([
            'filters' => [
                'start' => $dto->start->format('Y-m-d'),
                'end' => $dto->end->format('Y-m-d'),
            ],
            'fixtures' => $fixtures,
        ], context: ['groups' => [self::SHOW_PREDICTIONS, User::SHOW]]);
    }

    #[Route('/leaderboard', name: 'leaderboard', methods: ['GET'], format: 'json')]
    public function leaderboard(
        #[MapQueryString(
            validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY
        )] FixturesDto $dto = new FixturesDto()
    ): JsonResponse {
        $dto->transform();

        $users = $this->userRepository->fixturesLeaderboard(
            competition: $this->competitionRepository->findOneByCode($dto->competitionCode),
            season: $this->seasonRepository->findOneByYear($dto->year),
            start: $dto->start,
            end: $dto->end,
            limit: $dto->limit ?? 50,
        );

        return $this->json([
            'filters' => [
                'start' => $dto->start->format('Y-m-d'),
                'end' => $dto->end->format('Y-m-d'),
            ],
            'users' => $users,
        ], context: ['groups' => [self::SHOW_PREDICTIONS, User::SHOW]]);
    }

    #[Route('/make-predictions', name: 'makePredictions', methods: ['POST'], format: 'json')]
    public function makePredictions(#[MapRequestPayload(type: PredictionDto::class)] array $dtos): JsonResponse
    {
        try {
            $this->predictionsService->makePredictions($dtos, $this->getUser());
        } catch (FixtureHasStartedException $e) {
            return $this->json(['message' => 'Fixture has already started'], Response::HTTP_CONFLICT);
        }

        return $this->json(['message' => 'Success'], Response::HTTP_CREATED);
    }
}