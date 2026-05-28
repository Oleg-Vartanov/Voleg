<?php

namespace App\User\Http\V1;

use App\Core\Documentation\Attribute\Response\ItemResponse;
use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\Core\Enum\Group;
use App\Core\Http\ApiController;
use App\User\Entity\User;
use App\User\Http\V1\Trait\UserControllerTrait;
use App\User\Repository\UserRepository;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Get(
    tags: ['User'],
    responses: [
        new ItemResponse(
            type: User::class,
            description: 'User',
            groups: [Group::class, 'values']
        ),
        new NotFoundResponse('User not found'),
    ],
)]
#[Route('/users/{id}', name: 'user_get', methods: [Request::METHOD_GET])]
class UserGetAction extends ApiController
{
    use UserControllerTrait;

    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function __invoke(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if ($user === null) {
            throw new NotFoundHttpException();
        }

        return $this->json($user, context: ['groups' => $this->showGroups($id)]);
    }
}
