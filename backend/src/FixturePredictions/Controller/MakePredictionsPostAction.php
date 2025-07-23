<?php

namespace App\FixturePredictions\Controller;

use App\Core\DTO\Documentation\Validator\ValidationErrorResponse;
use App\FixturePredictions\DTO\Request\PredictionDto;
use App\FixturePredictions\Exception\FixtureHasStartedException;
use App\FixturePredictions\Service\PredictionsService;
use App\User\Entity\User;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Fixtures')]
#[Security(name: 'Bearer')]
#[OA\Response(response: Response::HTTP_CREATED, description: 'Success')]
#[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized')]
#[OA\Response(response: Response::HTTP_CONFLICT, description: 'Fixture has already started')]
#[OA\Response(
    response: Response::HTTP_UNPROCESSABLE_ENTITY,
    description: 'Validation errors',
    content: new Model(type: ValidationErrorResponse::class)
)]

#[Route(
    path: '/fixtures/make-predictions',
    name: 'make_predictions',
    methods: [Request::METHOD_POST],
    format: 'json'
)]
class MakePredictionsPostAction extends AbstractController
{
    public function __construct(private readonly PredictionsService $predictionsService)
    {
    }

    public function __invoke(
        #[MapRequestPayload(type: PredictionDto::class)] array $dtos
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $this->predictionsService->makePredictions($dtos, $user);
        } catch (FixtureHasStartedException $e) {
            return $this->json(['message' => 'Fixture has already started'], Response::HTTP_CONFLICT);
        }

        return $this->json(['message' => 'Success'], Response::HTTP_CREATED);
    }
}
