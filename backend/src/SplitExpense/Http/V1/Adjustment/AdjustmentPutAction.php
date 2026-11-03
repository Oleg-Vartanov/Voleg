<?php

namespace App\SplitExpense\Http\V1\Adjustment;

use App\Core\Documentation\Attribute\Response\ItemResponse;
use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\Core\Enum\Group;
use App\Core\Exception\UnprocessableException;
use App\Core\Http\ApiController;
use App\SplitExpense\Entity\Adjustment;
use App\SplitExpense\Http\V1\Request\AdjustmentDto;
use App\SplitExpense\Repository\AdjustmentRepository;
use App\SplitExpense\Service\AdjustmentService;
use App\User\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Put(
    description: 'Replaces the adjustment with the given representation. The whole payload is required.',
    security: [['Bearer' => []]],
    tags: ['Split Expense: Adjustment'],
    responses: [
        new ItemResponse(
            type: Adjustment::class,
            description: 'Adjustment updated',
            groups: [Group::public->value],
        ),
        new MessageResponse(Response::HTTP_BAD_REQUEST, 'Invalid data'),
        new NotFoundResponse('Adjustment not found'),
        new ValidationErrorResponse(),
    ],
)]
#[Route('/split-expense/adjustments/{id}', name: 'se_adjustment_put', methods: [Request::METHOD_PUT])]
class AdjustmentPutAction extends ApiController
{
    public function __construct(
        private readonly AdjustmentService $service,
        private readonly AdjustmentRepository $adjustmentRepository,
    ) {
    }

    public function __invoke(
        #[CurrentUser] User $user,
        int $id,
        #[MapRequestPayload] AdjustmentDto $dto,
    ): JsonResponse {
        $adjustment = $this->adjustmentRepository->find($id) ?? $this->notFound();

        // todo: move inside update
        if (!$this->service->canUpdate($user, $adjustment)) {
            $this->accessDenied();
        }

        try {
            $adjustment = $this->service->update($adjustment, $dto);
        } catch (UnprocessableException $e) {
            return $this->validationErrorResponse($e);
        }

        $this->adjustmentRepository->save($adjustment, true);

        return $this->json($adjustment, context: ['groups' => [Group::public->value]]);
    }
}
