<?php

namespace App\FixturePredictions\Http\V1\MakePredictions;

use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\UnauthorizedResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\Core\Exception\UnprocessableException;
use App\Core\Http\ApiController;
use App\FixturePredictions\Service\PredictionsService;
use App\User\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Post(
    security: [['Bearer' => []]],
    tags: ['FixturePredictions'],
    responses: [
        new MessageResponse(Response::HTTP_CREATED, 'Success'),
        new UnauthorizedResponse(),
        new MessageResponse(Response::HTTP_CONFLICT, 'Fixture has already started'),
        new ValidationErrorResponse(),
    ],
)]
#[Route(
    path: '/fixtures/make-predictions',
    name: 'make_predictions',
    methods: [Request::METHOD_POST],
    format: 'json'
)]
class MakePredictionsPostAction extends ApiController
{
    public function __construct(private readonly PredictionsService $predictionsService)
    {
    }

    /**
     * @param array<MakePredictionsRequest> $dtos
     */
    public function __invoke(
        #[MapRequestPayload(type: MakePredictionsRequest::class)] array $dtos
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $this->predictionsService->makePredictions($dtos, $user);
        } catch (UnprocessableException $e) {
            return $this->messageResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->messageResponse('Success', Response::HTTP_CREATED);
    }
}
