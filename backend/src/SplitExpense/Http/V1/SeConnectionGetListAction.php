<?php

namespace App\SplitExpense\Http\V1;

use App\Core\Documentation\Attribute\Response\ArrayResponse;
use App\Core\Enum\Group;
use App\Core\Http\ApiController;
use App\SplitExpense\Entity\SeConnection;
use App\SplitExpense\Repository\SeConnectionRepository;
use App\User\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Get(
    security: [['Bearer' => []]],
    tags: ['Split Expense'],
    responses: [
        new ArrayResponse(
            type: SeConnection::class,
            description: 'Split expense connections',
            groups: [Group::PUBLIC]
        ),
    ],
)]
#[Route('/split-expense/connections', name: 'se_connection_get_list', methods: [Request::METHOD_GET])]
class SeConnectionGetListAction extends ApiController
{
    public function __construct(
        private readonly SeConnectionRepository $conRepository,
    ) {
    }

    public function __invoke(
        #[CurrentUser] User $user,
        #[MapQueryParameter] int $offset = 0,
        #[MapQueryParameter] int $limit = 100,
    ): JsonResponse {
        return $this->json(
            $this->conRepository->listForUser($user, $offset, $limit),
            context: ['groups' => Group::PUBLIC],
        );
    }
}
