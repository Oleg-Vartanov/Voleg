<?php

namespace App\SplitExpense\Http\V1;

use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\Core\Http\ApiController;
use App\SplitExpense\Repository\SeAdjustmentRepository;
use App\SplitExpense\Service\SeAdjustmentService;
use App\User\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Delete(
    security: [['Bearer' => []]],
    tags: ['Split Expense: Adjustment'],
    responses: [
        new MessageResponse(Response::HTTP_NO_CONTENT, 'Adjustment deleted'),
        new NotFoundResponse('Adjustment not found'),
    ],
)]
#[Route('/split-expense/adjustments/{id}', name: 'se_adjustment_delete', methods: [Request::METHOD_DELETE])]
class SeAdjustmentDeleteAction extends ApiController
{
    public function __construct(
        private readonly SeAdjustmentService $service,
        private readonly SeAdjustmentRepository $adjustmentRepository,
    ) {
    }

    public function __invoke(#[CurrentUser] User $user, int $id): Response
    {
        $adjustment = $this->adjustmentRepository->find($id) ?? $this->notFound();

        if (!$this->service->hasAccess($user, $adjustment)) {
            $this->accessDenied();
        }

        $this->service->delete($adjustment);

        return $this->messageResponse('Adjustment deleted', Response::HTTP_NO_CONTENT);
    }
}
