<?php

namespace App\SplitExpense\Http\V1;

use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\Core\Http\ApiController;
use App\SplitExpense\Repository\SeConnectionRepository;
use App\User\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Delete(
    security: [['Bearer' => []]],
    tags: ['Split Expense'],
    responses: [
        new MessageResponse(Response::HTTP_NO_CONTENT, 'Connection deleted'),
        new NotFoundResponse('Connection not found'),
    ],
)]
#[Route(path: '/split-expense/connections/{id}', name: 'se_connection_delete', methods: [Request::METHOD_DELETE])]
class SeConnectionDeleteAction extends ApiController
{
    public function __construct(
        private readonly SeConnectionRepository $conRepository,
    ) {
    }

    public function __invoke(
        #[CurrentUser] User $user,
        int $id,
    ): Response {
        $connection = $this->conRepository->find($id) ?? $this->notFound();

        if (!$connection->hasUser($user)) {
            $this->accessDenied();
        }

        $this->conRepository->remove($connection, true);

        return $this->messageResponse('Connection deleted', Response::HTTP_NO_CONTENT);
    }
}
