<?php

namespace App\SplitExpense\Http\V1\Balance;

use App\Core\Documentation\Attribute\Response\ItemResponse;
use App\Core\Documentation\Attribute\Response\UnauthorizedResponse;
use App\Core\Enum\Group;
use App\Core\Http\ApiController;
use App\SplitExpense\Service\SeBalanceService;
use App\User\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Get(
    security: [['Bearer' => []]],
    tags: ['Split Expense: Balance'],
    responses: [
        new ItemResponse(
            type: SeBalanceResponse::class,
            description: 'Split expense balances of the current user',
            groups: [Group::public->value],
        ),
        new UnauthorizedResponse(),
    ],
)]
#[Route('/split-expense/balances', name: 'se_balance_get', methods: [Request::METHOD_GET])]
class SeBalanceGetAction extends ApiController
{
    public function __construct(
        private readonly SeBalanceService $balanceService,
    ) {
    }

    public function __invoke(#[CurrentUser] User $user): JsonResponse
    {
        return $this->json(
            $this->balanceService->forUser($user),
            context: ['groups' => Group::public->value],
        );
    }
}
