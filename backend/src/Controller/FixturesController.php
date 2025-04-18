<?php

namespace App\Controller;

use App\DTO\Documentation\Validator\ValidationErrorResponse;
use App\DTO\Fixtures\Request\FixturesDto;
use App\DTO\Fixtures\Request\PredictionDto;
use App\DTO\Fixtures\Request\SyncDto;
use App\Entity\User;
use App\Exception\FixtureHasStartedException;
use App\Interface\FixturesProviderInterface;
use App\Repository\CompetitionRepository;
use App\Repository\FixtureRepository;
use App\Repository\SeasonRepository;
use App\Repository\UserRepository;
use App\Service\Fixtures\PredictionsService;
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
            validationFailedStatusCode:Response::HTTP_UNPROCESSABLE_ENTITY
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