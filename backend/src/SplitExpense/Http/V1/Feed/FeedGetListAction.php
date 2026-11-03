<?php

namespace App\SplitExpense\Http\V1\Feed;

use App\Core\Documentation\Attribute\Response\ArrayResponse;
use App\Core\Documentation\Attribute\Response\UnauthorizedResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\Core\Enum\Group;
use App\Core\Http\ApiController;
use App\SplitExpense\Service\FeedService;
use App\User\Entity\User;
use Doctrine\DBAL\Exception;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Get(
    security: [['Bearer' => []]],
    tags: ['Split Expense: Feed'],
    responses: [
        new ArrayResponse(
            type: FeedItem::class,
            description: 'Expenses and adjustments, newest first',
            groups: [Group::public->value],
        ),
        new UnauthorizedResponse(),
        new ValidationErrorResponse(),
    ],
)]
#[Route('/split-expense/feed', name: 'se_feed_get_list', methods: [Request::METHOD_GET])]
class FeedGetListAction extends ApiController
{
    public function __construct(
        private readonly FeedService $feedService,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(
        #[CurrentUser] User $user,
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        FeedRequest $dto = new FeedRequest(),
    ): JsonResponse {
        $types = $dto->selectedTypes();
        $items = $this->feedService->listForUser($user, $types, $dto->offset, $dto->limit);
        $totalCount = $this->feedService->countForUser($user, $types);

        return $this->json(
            $items,
            headers: ['X-Total-Count' => (string) $totalCount],
            context: ['groups' => Group::public->value],
        );
    }
}
