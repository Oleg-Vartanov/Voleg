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
use App\SplitExpense\Http\V1\Request\SeConnectionCreateDto;
use App\SplitExpense\Repository\SeConnectionRepository;
use App\SplitExpense\Service\SeConnectionService;
use App\User\Entity\User;
use App\User\Http\V1\Trait\UserControllerTrait;
use App\User\Repository\UserRepository;
use InvalidArgumentException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Post(
    security: [['Bearer' => []]],
    tags: ['Split Expense'],
    responses: [
        new ItemResponse(
            type: SeConnection::class,
            responseCode: Response::HTTP_CREATED,
            description: 'Connection created',
            groups: [Group::PUBLIC]
        ),
        new MessageResponse(Response::HTTP_BAD_REQUEST, 'Invalid data'),
        new AccessDeniedResponse(),
        new NotFoundResponse('User not found'),
        new ValidationErrorResponse(),
    ],
)]
#[Route('/split-expense/connections', name: 'se_connection_post', methods: [Request::METHOD_POST])]
class SeConnectionPostAction extends ApiController
{
    use UserControllerTrait;

    public function __construct(
        private readonly SeConnectionService $service,
        private readonly SeConnectionRepository $connectionRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(
        #[CurrentUser] User $user,
        #[MapRequestPayload] SeConnectionCreateDto $dto,
    ): JsonResponse {
        $requestedUser = $this->userRepository->find($dto->connectionUserId) ?? $this->notFound();

        try {
            $connection = $this->service->create($user, $requestedUser);
        } catch (InvalidArgumentException $e) {
            return $this->messageResponse($e->getMessage(), 400);
        }
        $this->connectionRepository->save($connection, true);
        $this->service->requestConnection($connection);

        return $this->json(
            $connection,
            Response::HTTP_CREATED,
            context: ['groups' => Group::PUBLIC],
        );
    }
}
