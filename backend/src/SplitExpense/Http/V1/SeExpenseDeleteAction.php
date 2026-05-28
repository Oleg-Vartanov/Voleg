<?php

namespace App\SplitExpense\Http\V1;

use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\Core\Http\ApiController;
use App\SplitExpense\Repository\SeExpenseRepository;
use App\SplitExpense\Service\SeExpenseService;
use App\User\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Delete(
    security: [['Bearer' => []]],
    tags: ['Split Expense'],
    responses: [
        new MessageResponse(Response::HTTP_NO_CONTENT, 'Expense deleted'),
        new NotFoundResponse('Expense not found'),
    ],
)]
#[Route('/split-expense/expenses/{id}', name: 'se_expense_delete', methods: [Request::METHOD_DELETE])]
class SeExpenseDeleteAction extends ApiController
{
    public function __construct(
        private readonly SeExpenseService $service,
        private readonly SeExpenseRepository $expenseRepository,
    ) {
    }

    public function __invoke(#[CurrentUser] User $user, int $id): Response
    {
        $expense = $this->expenseRepository->find($id) ?? $this->notFound();

        // todo: only owner can delete? or maybe soft delete?
        if (!$this->service->hasAccess($user, $expense)) {
            $this->accessDenied();
        }

        $this->service->delete($expense);

        return $this->messageResponse('Expense deleted', Response::HTTP_NO_CONTENT);
    }
}
