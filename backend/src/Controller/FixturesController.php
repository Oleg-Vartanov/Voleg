<?php

namespace App\Controller;

use App\DTO\Fixtures\FixturesRequestDto;
use App\DTO\Fixtures\SyncRequestDto;
use App\DTO\Validator\ValidationErrorResponse;
use App\Entity\FixturePrediction;
use App\Interface\FixturesProviderInterface;
use App\Repository\CompetitionRepository;
use App\Repository\FixtureRepository;
use App\Repository\SeasonRepository;
use DateTime;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag(name: 'Fixtures')]
#[Route('/fixtures', name: 'fixtures_')]
class FixturesController extends ApiController
{
    public function __construct(
        protected ValidatorInterface $validator,
        private readonly SerializerInterface $serializer,
        private readonly FixturesProviderInterface $fixturesProvider,
        private readonly CompetitionRepository $competitionRepository,
        private readonly SeasonRepository $seasonRepository,
        private readonly FixtureRepository $fixtureRepository,
    ) {
    }

    /* OpenAi Documentation */
    #[OA\RequestBody(content: new Model(type: SyncRequestDto::class))]
    #[OA\Response(response: Response::HTTP_OK, description: 'Fixtures synced')]
    #[OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Dont have rights')]
    #[OA\Response(response: Response::HTTP_UNPROCESSABLE_ENTITY, description: 'Validation errors',
        content: new Model(type: ValidationErrorResponse::class))]

    #[Route('/sync', name: 'sync', methods: ['POST'])]
    public function sync(Request $request): Response {
        /** @var SyncRequestDto $dto */
        $dto = $this->serializer->denormalize(
            $request->getPayload()->all(), SyncRequestDto::class
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
        )] FixturesRequestDto $dto = new FixturesRequestDto()
    ): JsonResponse {
        $start = $dto->start === null ? (new DateTime())->modify('-5 days') : new DateTime($dto->start);
        $end = $dto->end === null ? (new DateTime())->modify('+5 days') : new DateTime($dto->end);

        $fixtures = $this->fixtureRepository->filter(
            user: $this->getUser(),
            competition: $this->competitionRepository->findOneByCode($dto->countryCode),
            season: $this->seasonRepository->findOneByYear($dto->year),
            start: $start,
            end: $end,
        );

        return $this->json([
            'filters' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
            'fixtures' => $fixtures,
        ], context: ['groups' => [FixturePrediction::SHOW_PREDICTIONS]]);
    }
}