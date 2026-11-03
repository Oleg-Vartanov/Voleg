<?php

namespace App\SplitExpense\Http\V1\Adjustment;

use App\Core\Documentation\Attribute\Response\ItemResponse;
use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\Core\Enum\Group;
use App\Core\Http\ApiController;
use App\SplitExpense\Entity\Adjustment;
use App\SplitExpense\Repository\AdjustmentRepository;
use App\SplitExpense\Service\AdjustmentService;
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
            type: Adjustment::class,
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
class AdjustmentGetAction extends ApiController
{
    public function __construct(
        private readonly AdjustmentRepository $adjustmentRepository,
        private readonly AdjustmentService $service,
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
