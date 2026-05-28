<?php

namespace App\Core\Http;

use App\Core\Documentation\Attribute\Response\ArrayResponse;
use App\Core\Documentation\Attribute\Response\UnauthorizedResponse;
use App\Core\Entity\Currency;
use App\Core\Repository\CurrencyRepository;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Get(
    security: [['Bearer' => []]],
    tags: ['Split Expense'],
    responses: [
        new ArrayResponse(type: Currency::class, description: 'Currencies'),
        new UnauthorizedResponse(),
    ],
)]
#[Route('/v1/currencies', name: 'currency_get_list', methods: [Request::METHOD_GET])]
class CurrencyGetListAction extends ApiController
{
    public function __construct(
        private readonly CurrencyRepository $currencyRepository,
    ) {
    }

    public function __invoke(
        #[MapQueryParameter] int $offset = 0,
        #[MapQueryParameter] int $limit = 100,
    ): JsonResponse {
        return $this->json(
            $this->currencyRepository->list($offset, $limit)
        );
    }
}
