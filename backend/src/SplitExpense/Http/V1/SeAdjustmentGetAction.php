<?php

namespace App\SplitExpense\Http\V1;

use App\Core\Documentation\Attribute\Response\ItemResponse;
use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\Core\Enum\Group;
use App\Core\Http\ApiController;
use App\SplitExpense\Entity\SeAdjustment;
use App\SplitExpense\Repository\SeAdjustmentRepository;
use App\SplitExpense\Service\SeAdjustmentService;
use App\User\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Get(
    security: [['Bearer' => []]],
    tags: ['Split Expense: Adjustment'],
    responses: [
        new ItemResponse(
            type: SeAdjustment::class,
            description: 'Split expense adjustment',
            groups: [Group::public->value],
        ),
        new NotFoundResponse('Adjustment not found'),
    ],
)]
#[Route(
    path: '/split-expense/adjustments/{id}',
    name: 'se_adjustment_get',
    methods: [Request::METHOD_GET],
)]
class SeAdjustmentGetAction extends ApiController
{
    public function __construct(
        private readonly SeAdjustmentRepository $adjustmentRepository,
        private readonly SeAdjustmentService $service,
    ) {
    }

    public function __invoke(#[CurrentUser] User $user, int $id): JsonResponse
    {
        $adjustment = $this->adjustmentRepository->find($id) ?? $this->notFound();

        if (!$this->service->hasAccess($user, $adjustment)) {
            $this->accessDenied();
        }

        return $this->json($adjustment, context: ['groups' => Group::public->value]);
    }
}
