<?php

namespace App\Controller;

use App\DTO\Fixtures\SyncRequestDto;
use App\DTO\Validator\ValidationErrorResponse;
use App\Enum\Fixtures\CompetitionCodeEnum;
use App\Interface\FixturesProviderInterface;
use App\Repository\CompetitionRepository;
use App\Repository\FixtureRepository;
use App\Repository\SeasonRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route('/predictions', name: 'predictions', methods: ['GET'])]
    public function fixtures(): JsonResponse
    {
        // TODO: Remove hardcore and create some functionality.
        $season = $this->seasonRepository->findOneByYear(2024);
        $competition = $this->competitionRepository->findOneByCode(CompetitionCodeEnum::EPL->value);

        $fixtures = $this->fixtureRepository->filter($this->getUser(), $competition, $season);

        // TODO: Filter by matchday?
        
        return $this->json($fixtures, context: ['groups' => ['ShowPredictions']]);
    }
}