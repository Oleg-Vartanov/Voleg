<?php

namespace App\SplitExpense\Http\V1\Insights;

use App\Core\Documentation\Attribute\Response\ItemResponse;
use App\Core\Documentation\Attribute\Response\UnauthorizedResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\Core\Enum\Group;
use App\Core\Exception\UnprocessableException;
use App\Core\Http\ApiController;
use App\Core\ValueObject\Validator\Violation;
use App\SplitExpense\Service\SeInsightsService;
use App\User\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Get(
    security: [['Bearer' => []]],
    tags: ['Split Expense: Insights'],
    responses: [
        new ItemResponse(
            type: SeInsightsResponse::class,
            description: 'Total expense cost paid by the current user and optional connected payers',
            groups: [Group::public->value],
        ),
        new ValidationErrorResponse(),
        new UnauthorizedResponse(),
    ],
)]
#[Route('/split-expense/insights', name: 'se_insights_get', methods: [Request::METHOD_GET])]
class SeInsightsGetAction extends ApiController
{
    public function __construct(
        private readonly SeInsightsService $insightsService,
    ) {
    }

    public function __invoke(
        #[CurrentUser] User $user,
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        SeInsightsRequest $dto = new SeInsightsRequest(),
    ): JsonResponse {
        try {
            $insights = $this->insightsService->forUser(
                $user,
                $dto->offset,
                $dto->limit,
                $dto->categoryId,
                $dto->userIds,
            );
        } catch (UnprocessableException $e) {
            return $this->validationErrorResponse(
                new Violation($e->propertyPath, $e->getMessage())
            );
        }

        return $this->json(
            $insights,
            context: ['groups' => Group::public->value],
        );
    }
}
