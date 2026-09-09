<?php

namespace App\SplitExpense\Http\V1;

use App\Core\Documentation\Attribute\Response\ItemResponse;
use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\Core\Enum\Group;
use App\Core\Exception\UnprocessableException;
use App\Core\Http\ApiController;
use App\Core\ValueObject\Validator\Violation;
use App\SplitExpense\Entity\SeAdjustment;
use App\SplitExpense\Http\V1\Request\SeAdjustmentDto;
use App\SplitExpense\Repository\SeAdjustmentRepository;
use App\SplitExpense\Service\SeAdjustmentService;
use App\User\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Post(
    security: [['Bearer' => []]],
    tags: ['Split Expense: Adjustment'],
    responses: [
        new ItemResponse(
            type: SeAdjustment::class,
            description: 'Adjustment created',
            groups: [Group::public->value],
        ),
        new MessageResponse(Response::HTTP_BAD_REQUEST, 'Invalid data'),
        new ValidationErrorResponse(),
    ],
)]
#[Route('/split-expense/adjustments', name: 'se_adjustment_post', methods: [Request::METHOD_POST])]
class SeAdjustmentPostAction extends ApiController
{
    public function __construct(
        private readonly SeAdjustmentService $service,
        private readonly SeAdjustmentRepository $adjustmentRepository,
    ) {
    }

    public function __invoke(
        #[CurrentUser] User $user,
        #[MapRequestPayload] SeAdjustmentDto $dto,
    ): JsonResponse {
        try {
            $adjustment = $this->service->create($user, $dto);
        } catch (UnprocessableException $e) {
            return $this->validationErrorResponse(
                new Violation($e->propertyPath, $e->getMessage())
            );
        }

        $this->adjustmentRepository->save($adjustment, true);

        return $this->json(
            $adjustment,
            Response::HTTP_CREATED,
            context: ['groups' => Group::public->value],
        );
    }
}
