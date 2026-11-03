<?php

namespace App\SplitExpense\Http\V1\Connection;

use App\Core\Documentation\Attribute\Response\AccessDeniedResponse;
use App\Core\Documentation\Attribute\Response\ItemResponse;
use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\Core\Enum\Group;
use App\Core\Http\ApiController;
use App\SplitExpense\Entity\Connection;
use App\SplitExpense\Http\V1\Request\ConnectionRequestDto;
use App\SplitExpense\Repository\ConnectionRepository;
use App\SplitExpense\Service\ConnectionService;
use App\User\Entity\User;
use App\User\Http\V1\Trait\UserControllerTrait;
use App\User\Repository\UserRepository;
use LogicException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Post(
    security: [['Bearer' => []]],
    tags: ['Split Expense: Connection'],
    responses: [
        new ItemResponse(
            type: Connection::class,
            responseCode: Response::HTTP_CREATED,
            description: 'Connection request created',
            groups: [Group::public->value]
        ),
        new MessageResponse(Response::HTTP_BAD_REQUEST, 'Invalid data'),
        new AccessDeniedResponse(),
        new NotFoundResponse('User not found'),
        new ValidationErrorResponse(),
    ],
)]
#[Route('/split-expense/connections/request', name: 'se_connection_request', methods: [Request::METHOD_POST])]
class ConnectionRequestAction extends ApiController
{
    use UserControllerTrait;

    public function __construct(
        private readonly ConnectionService $service,
        private readonly ConnectionRepository $connectionRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(
        #[CurrentUser] User $user,
        #[MapRequestPayload(validationFailedStatusCode: 422)] ConnectionRequestDto $dto,
    ): JsonResponse {
        $requestedUser = $this->userRepository->find($dto->connectionUserId) ?? $this->notFound();

        try {
            $connection = $this->service->create($user, $requestedUser);
        } catch (LogicException $e) {
            return $this->messageResponse($e->getMessage(), 400);
        }
        $this->connectionRepository->save($connection, true);
        $this->service->requestConnection($connection);

        return $this->json(
            $connection,
            Response::HTTP_CREATED,
            context: ['groups' => Group::public->value],
        );
    }
}
