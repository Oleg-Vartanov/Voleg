<?php

namespace App\SplitExpense\Http\V1\Expense;

use App\Core\Documentation\Attribute\Response\ItemResponse;
use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\Core\Enum\Group;
use App\Core\Http\ApiController;
use App\Core\ValueObject\Validator\Violation;
use App\SplitExpense\Entity\Expense;
use App\SplitExpense\Exception\ExpenseSplitException;
use App\SplitExpense\Http\V1\Request\ExpenseDto;
use App\SplitExpense\Repository\ExpenseRepository;
use App\SplitExpense\Service\ExpenseService;
use App\User\Entity\User;
use App\User\Http\V1\Trait\UserControllerTrait;
use DateMalformedStringException;
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
    tags: ['Split Expense: Expense'],
    responses: [
        new ItemResponse(
            type: Expense::class,
            description: 'Expense created',
            groups: [Group::public->value],
        ),
        new MessageResponse(Response::HTTP_BAD_REQUEST, 'Invalid data'),
        new NotFoundResponse('User not found'),
        new ValidationErrorResponse(),
    ],
)]
#[Route('/split-expense/expenses/{id}', name: 'se_expense_post', methods: [Request::METHOD_POST])]
class ExpensePostAction extends ApiController
{
    use UserControllerTrait;

    public function __construct(
        private readonly ExpenseService $service,
        private readonly ExpenseRepository $expenseRepository,
    ) {
    }

    public function __invoke(
        #[CurrentUser] User $user,
        int $id,
        #[MapRequestPayload] ExpenseDto $dto,
    ): JsonResponse {
        try {
            $expense = $this->service->create($user, $dto);
        } catch (LogicException | DateMalformedStringException $e) {
            return $this->messageResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (ExpenseSplitException $e) {
            return $this->validationErrorResponse(
                new Violation('splits', $e->getMessage())
            );
        }

        $this->expenseRepository->save($expense, true);

        return $this->json(
            $expense,
            Response::HTTP_CREATED,
            context: ['groups' => Group::public->value],
        );
    }
}
