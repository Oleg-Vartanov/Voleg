<?php

namespace App\SplitExpense\Http\V1;

use App\Core\Documentation\Attribute\Response\ArrayResponse;
use App\Core\Documentation\Attribute\Response\UnauthorizedResponse;
use App\Core\Enum\Group;
use App\Core\Http\ApiController;
use App\SplitExpense\Entity\SeExpense;
use App\SplitExpense\Repository\SeExpenseRepository;
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
            type: SeExpense::class,
            description: 'Split expenses',
            groups: [Group::public->value],
        ),
        new UnauthorizedResponse(),
    ],
)]
#[Route('/split-expense/expenses', name: 'se_expense_get_list', methods: [Request::METHOD_GET])]
class SeExpenseGetListAction extends ApiController
{
    public function __construct(
        private readonly SeExpenseRepository $expenseRepository,
    ) {
    }

    public function __invoke(
        #[CurrentUser] User $user,
        #[MapQueryParameter] int $offset = 0,
        #[MapQueryParameter] int $limit = 100,
    ): JsonResponse {
        return $this->json(
            $this->expenseRepository->listForUser($user, $offset, $limit),
            context: ['groups' => Group::public->value],
        );
    }
}
