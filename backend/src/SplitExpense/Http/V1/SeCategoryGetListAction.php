<?php

namespace App\SplitExpense\Http\V1;

use App\Core\Documentation\Attribute\Response\ArrayResponse;
use App\Core\Http\ApiController;
use App\SplitExpense\Entity\SeCategory;
use App\SplitExpense\Repository\SeCategoryRepository;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Get(
    security: [['Bearer' => []]],
    tags: ['Split Expense'],
    responses: [
        new ArrayResponse(
            type: SeCategory::class,
            responseCode: Response::HTTP_OK,
            description: 'Categories',
        )
    ],
)]
#[Route('/split-expense/categories', name: 'se_category_get_list', methods: [Request::METHOD_GET])]
class SeCategoryGetListAction extends ApiController
{
    public function __construct(
        private readonly SeCategoryRepository $categoryRepository,
    ) {
    }

    public function __invoke(
        #[MapQueryParameter] int $offset = 0,
        #[MapQueryParameter] int $limit = 100,
    ): JsonResponse {
        return $this->json(
            $this->categoryRepository->list($offset, $limit),
        );
    }
}
