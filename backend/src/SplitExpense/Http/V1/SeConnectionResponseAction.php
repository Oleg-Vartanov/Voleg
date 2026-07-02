<?php

namespace App\SplitExpense\Http\V1;

use App\Core\Documentation\Attribute\Response\AccessDeniedResponse;
use App\Core\Documentation\Attribute\Response\ItemResponse;
use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\Core\Enum\Group;
use App\Core\Http\ApiController;
use App\SplitExpense\Entity\SeConnection;
use App\SplitExpense\Http\V1\Request\SeConnectionResponseDto;
use App\SplitExpense\Repository\SeConnectionRepository;
use App\SplitExpense\Service\SeConnectionService;
use App\User\Entity\User;
use LogicException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Post(
    security: [['Bearer' => []]],
    tags: ['Split Expense: Connection'],
    responses: [
        new ItemResponse(
            type: SeConnection::class,
            description: 'Connection response recorded',
            groups: [Group::public->value],
        ),
        new MessageResponse(400, 'Invalid data'),
        new AccessDeniedResponse(),
        new NotFoundResponse('Connection not found'),
        new ValidationErrorResponse(),
    ],
)]
#[Route('/split-expense/connections/{id}/response', name: 'se_connection_response', methods: [Request::METHOD_POST])]
class SeConnectionResponseAction extends ApiController
{
    public function __construct(
        private readonly SeConnectionService $service,
        private readonly SeConnectionRepository $connectionRepository,
    ) {
    }

    public function __invoke(
        #[CurrentUser] User $user,
        int $id,
        #[MapRequestPayload(validationFailedStatusCode: 422)] SeConnectionResponseDto $dto,
    ): JsonResponse {
        $connection = $this->connectionRepository->find($id) ?? $this->notFound();

        try {
            $this->service->respond($user, $connection, $dto->status);
        } catch (LogicException $e) {
            return $this->messageResponse($e->getMessage(), 400);
        }

        $this->connectionRepository->save($connection, true);

        return $this->json($connection, context: ['groups' => Group::public->value]);
    }
}
