<?php

namespace App\SplitExpense\Http\V1\Adjustment;

use App\Core\Documentation\Attribute\Response\ArrayResponse;
use App\Core\Documentation\Attribute\Response\UnauthorizedResponse;
use App\Core\Enum\Group;
use App\Core\Http\ApiController;
use App\SplitExpense\Entity\Adjustment;
use App\SplitExpense\Repository\AdjustmentRepository;
use App\User\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Get(
    security: [['Bearer' => []]],
    tags: ['Split Expense: Adjustment'],
    responses: [
        new ArrayResponse(
            type: Adjustment::class,
            description: 'Split expense adjustments',
            groups: [Group::public->value],
        ),
        new UnauthorizedResponse(),
    ],
)]
#[Route('/split-expense/adjustments', name: 'se_adjustment_get_list', methods: [Request::METHOD_GET])]
class AdjustmentGetListAction extends ApiController
{
    public function __construct(
        private readonly AdjustmentRepository $adjustmentRepository,
    ) {
    }

    public function __invoke(
        #[CurrentUser] User $user,
        #[MapQueryParameter] int $offset = 0,
        #[MapQueryParameter] int $limit = 100,
    ): JsonResponse {
        $adjustments = $this->adjustmentRepository->listForUser($user, $offset, $limit);
        $totalCount = $this->adjustmentRepository->countForUser($user);

        return $this->json(
            $adjustments,
            headers: ['X-Total-Count' => (string) $totalCount],
            context: ['groups' => Group::public->value],
        );
    }
}
