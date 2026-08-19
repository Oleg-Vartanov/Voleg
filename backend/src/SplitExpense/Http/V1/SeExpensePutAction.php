<?php

namespace App\SplitExpense\Http\V1;

use App\Core\Documentation\Attribute\Response\ItemResponse;
use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\Core\Enum\Group;
use App\Core\Exception\NotFoundException;
use App\Core\Http\ApiController;
use App\Core\ValueObject\Validator\Violation;
use App\SplitExpense\Entity\SeExpense;
use App\SplitExpense\Exception\SeExpenseSplitException;
use App\SplitExpense\Http\V1\Request\SeExpenseDto;
use App\SplitExpense\Repository\SeExpenseRepository;
use App\SplitExpense\Service\SeExpenseService;
use App\User\Entity\User;
use DateMalformedStringException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Put(
    description: 'Replaces the expense with the given representation. The whole payload is required.',
    security: [['Bearer' => []]],
    tags: ['Split Expense: Expense'],
    responses: [
        new ItemResponse(
            type: SeExpense::class,
            description: 'Expense updated',
            groups: [Group::public->value],
        ),
        new MessageResponse(Response::HTTP_BAD_REQUEST, 'Invalid data'),
        new NotFoundResponse('Expense not found'),
        new ValidationErrorResponse(),
    ],
)]
#[Route('/split-expense/expenses/{id}', name: 'se_expense_put', methods: [Request::METHOD_PUT])]
class SeExpensePutAction extends ApiController
{
    public function __construct(
        private readonly SeExpenseService $service,
        private readonly SeExpenseRepository $expenseRepository,
    ) {
    }

    public function __invoke(
        #[CurrentUser] User $user,
        int $id,
        #[MapRequestPayload] SeExpenseDto $dto,
    ): JsonResponse {
        $expense = $this->expenseRepository->find($id) ?? $this->notFound();

        if (!$this->service->hasAccess($user, $expense)) {
            $this->accessDenied();
        }

        try {
            $expense = $this->service->update($user, $expense, $dto);
        } catch (DateMalformedStringException $e) {
            return $this->messageResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (SeExpenseSplitException $e) {
            return $this->validationErrorResponse(
                new Violation('splits', $e->getMessage())
            );
        } catch (NotFoundException $e) {
            return $this->validationErrorResponse(
                new Violation($e->tag, $e->getMessage())
            );
        }

        $this->expenseRepository->save($expense, true);

        return $this->json($expense, context: ['groups' => [Group::public->value]]);
    }
}
