<?php

namespace App\FixturePredictions\Controller;

use App\Core\DTO\Documentation\Validator\ValidationErrorResponse;
use App\FixturePredictions\DTO\Request\FixturesDto;
use App\FixturePredictions\DTO\Request\PredictionDto;
use App\FixturePredictions\DTO\Request\SyncDto;
use App\FixturePredictions\Exception\FixtureHasStartedException;
use App\FixturePredictions\Interface\FixturesProviderInterface;
use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\FixtureRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use App\FixturePredictions\Service\PredictionsService;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Fixtures')]
#[Security(name: 'Bearer')]
#[Route('/fixtures', name: 'fixtures_')]
class FixturesController extends AbstractController
{
    const SHOW_PREDICTIONS = 'ShowPredictions';

    #[OA\Response(response: Response::HTTP_OK, description: 'Fixtures synced')]
    #[OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Dont have rights')]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Validation errors',
        content: new Model(type: ValidationErrorResponse::class)
    )]
    #[Route('/sync', name: 'sync', methods: ['POST'])]
    public function sync(
        CompetitionRepository $competitionRepository,
        SeasonRepository $seasonRepository,
        FixturesProviderInterface $fixturesProvider,
        #[MapRequestPayload] SyncDto $dto,
    ): Response {
        $competition = $competitionRepository->findOneByCode($dto->competitionCode) ?? throw new NotFoundHttpException();
        $season = $seasonRepository->findOneByYear($dto->seasonYear) ?? throw new NotFoundHttpException();

        $fixturesProvider->syncTeams($competition, $season);
        $fixturesProvider->syncFixtures($competition, $season);

        return new Response('Synced');
    }

    #[OA\Response(response: Response::HTTP_OK, description: 'OK')]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Validation errors',
        content: new Model(type: ValidationErrorResponse::class)
    )]
    #[Route('/predictions', name: 'predictions', methods: ['GET'], format: 'json')]
    public function fixtures(
        CompetitionRepository $competitionRepository,
        SeasonRepository $seasonRepository,
        FixtureRepository $fixtureRepository,
        UserRepository $userRepository,
        #[MapQueryString(
            validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY
        )] FixturesDto $dto = new FixturesDto(),
    ): JsonResponse {
        $users = empty($dto->userIds) ? [] : $userRepository->findBy(['id' => $dto->userIds]);
        array_unshift($users, $this->getUser());

        $competition = $competitionRepository->findOneByCode($dto->competitionCode);

        $fixtures = $fixtureRepository->filter(
            users: $users,
            competition: $competition,
            season: $seasonRepository->findOneByYear($dto->year),
            start: $dto->start,
            end: $dto->end,
        );

        return $this->json([
            'filters' => [
                'start' => $dto->start->format('Y-m-d'),
                'end' => $dto->end->format('Y-m-d'),
                'competition' => $competition?->getCode(),
            ],
            'fixtures' => $fixtures,
        ], context: ['groups' => [self::SHOW_PREDICTIONS, User::SHOW]]);
    }

    #[OA\Response(response: Response::HTTP_OK, description: 'OK')]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Validation errors',
        content: new Model(type: ValidationErrorResponse::class)
    )]
    #[Route('/leaderboard', name: 'leaderboard', methods: ['GET'], format: 'json')]
    public function leaderboard(
        CompetitionRepository $competitionRepository,
        SeasonRepository $seasonRepository,
        UserRepository $userRepository,
        #[MapQueryString(
            validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY
        )] FixturesDto $dto = new FixturesDto()
    ): JsonResponse {
        $competition = $competitionRepository->findOneByCode($dto->competitionCode);

        $users = $userRepository->fixturesLeaderboard(
            competition: $competition,
            season: $seasonRepository->findOneByYear($dto->year),
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
        ], context: ['groups' => [self::SHOW_PREDICTIONS, User::SHOW]]);
    }

    #[OA\Response(response: Response::HTTP_CREATED, description: 'Success')]
    #[OA\Response(response: Response::HTTP_CONFLICT, description: 'Fixture has already started')]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Validation errors',
        content: new Model(type: ValidationErrorResponse::class)
    )]
    #[Route('/make-predictions', name: 'makePredictions', methods: ['POST'], format: 'json')]
    public function makePredictions(
        PredictionsService $predictionsService,
        #[MapRequestPayload(type: PredictionDto::class)] array $dtos
    ): JsonResponse {
        try {
            $predictionsService->makePredictions($dtos, $this->getUser());
        } catch (FixtureHasStartedException $e) {
            return $this->json(['message' => 'Fixture has already started'], Response::HTTP_CONFLICT);
        }

        return $this->json(['message' => 'Success'], Response::HTTP_CREATED);
    }
}