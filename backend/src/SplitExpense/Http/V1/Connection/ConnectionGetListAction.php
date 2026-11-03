<?php

namespace App\SplitExpense\Http\V1\Connection;

use App\Core\Documentation\Attribute\Response\ArrayResponse;
use App\Core\Enum\Group;
use App\Core\Http\ApiController;
use App\SplitExpense\Entity\Connection;
use App\SplitExpense\Enum\ConnectionDirectionEnum;
use App\SplitExpense\Enum\ConnectionStatusEnum;
use App\SplitExpense\Repository\ConnectionRepository;
use App\User\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Get(
    security: [['Bearer' => []]],
    tags: ['Split Expense: Connection'],
    responses: [
        new ArrayResponse(
            type: Connection::class,
            description: 'Split expense connections',
            groups: [Group::public->value]
        ),
    ],
)]
#[Route('/split-expense/connections', name: 'se_connection_get_list', methods: [Request::METHOD_GET])]
class ConnectionGetListAction extends ApiController
{
    public function __construct(
        private readonly ConnectionRepository $conRepository,
    ) {
    }

    public function __invoke(
        #[CurrentUser] User $user,
        #[MapQueryParameter] int $offset = 0,
        #[MapQueryParameter] int $limit = 100,
        #[MapQueryParameter] ?ConnectionStatusEnum $status = null,
        #[MapQueryParameter] bool $usersOnly = false,
        #[MapQueryParameter] ?string $username = null,
        #[MapQueryParameter] ?ConnectionDirectionEnum $direction = null,
    ): JsonResponse {
        $connections = $this->conRepository->listForUser($user, $offset, $limit, $status, $username, $direction);

        if ($usersOnly) {
            $users = [];
            foreach ($connections as $connection) {
                $users[] = $user->getId() === $connection->getUserA()->getId()
                    ? $connection->getUserB()
                    : $connection->getUserA();
            }
        }

        $totalCount = $this->conRepository->countForUser($user, $status, $username, $direction);

        return $this->json(
            $usersOnly ? $users : $connections,
            headers: ['X-Total-Count' => (string) $totalCount],
            context: ['groups' => Group::public->value],
        );
    }
}
