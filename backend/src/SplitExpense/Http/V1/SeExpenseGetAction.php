<?php

namespace App\SplitExpense\Http\V1;

use App\Core\Documentation\Attribute\Response\ItemResponse;
use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\Core\Enum\Group;
use App\Core\Http\ApiController;
use App\SplitExpense\Entity\SeExpense;
use App\SplitExpense\Repository\SeExpenseRepository;
use App\SplitExpense\Service\SeExpenseService;
use App\User\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Get(
    security: [['Bearer' => []]],
    tags: ['Split Expense'],
    responses: [
        new ItemResponse(
            type: SeExpense::class,
            description: 'Split expense',
            groups: [Group::public->value],
        ),
        new NotFoundResponse('Expense not found'),
    ],
)]
#[Route(
    path: '/split-expense/expenses/{id}',
    name: 'se_expense_get',
    methods: [Request::METHOD_GET],
)]
class SeExpenseGetAction extends ApiController
{
    public function __construct(
        private readonly SeExpenseRepository $expenseRepository,
        private readonly SeExpenseService $service,

    ) {
    }

    public function __invoke(#[CurrentUser] User $user, int $id): JsonResponse
    {
        $expense = $this->expenseRepository->find($id) ?? $this->notFound();

        if (!$this->service->hasAccess($user, $expense)) {
            $this->accessDenied();
        }

        return $this->json($expense, context: ['groups' => Group::public->value]);
    }
}
