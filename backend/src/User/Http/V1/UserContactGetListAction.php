<?php

namespace App\User\Http\V1;

use App\Core\Documentation\Attribute\Response\ArrayResponse;
use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\Core\Enum\Group;
use App\Core\Http\ApiController;
use App\User\Entity\User;
use App\User\Http\V1\Trait\UserControllerTrait;
use App\User\Repository\UserContactRepository;
use App\User\Repository\UserRepository;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Get(
    security: [['Bearer' => []]],
    tags: ['User'],
    responses: [
        new ArrayResponse(
            type: User::class,
            responseCode: Response::HTTP_OK,
            description: 'User contacts',
            groups: [Group::public->value],
        ),
        new NotFoundResponse('User not found'),
    ],
)]
#[Route('/users/{id}/contacts', name: 'user_contact_get_list', methods: [Request::METHOD_GET])]
class UserContactGetListAction extends ApiController
{
    use UserControllerTrait;

    public function __construct(
        private readonly UserContactRepository $userContactRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(
        int $id,
        #[MapQueryParameter] int $offset = 0,
        #[MapQueryParameter] int $limit = 100,
    ): JsonResponse {
        $this->checkModifyAccess($id);
        $user = $this->userRepository->findById($id) ?? $this->notFound();

        return $this->json(
            $this->userContactRepository->listContacts($user, $offset, $limit),
            context: ['groups' => $this->showGroups()]
        );
    }
}
