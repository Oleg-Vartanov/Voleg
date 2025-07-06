<?php

namespace App\User\Controller;

use App\User\Controller\Trait\UserControllerTrait;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'User')]
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'Users',
    content: new OA\JsonContent(
        type: 'array',
        items: new OA\Items(ref: new Model(type: User::class, groups: [User::SHOW]))
    )
)]

#[Route('/users', name: 'user_get_list', methods: [Request::METHOD_GET])]
class UserGetListAction extends AbstractController
{
    use UserControllerTrait;

    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function __invoke(
        #[MapQueryParameter] ?string $tag,
        #[MapQueryParameter] int $offset = 0,
        #[MapQueryParameter] int $limit = 100,
    ): JsonResponse {
        return $this->json(
            $this->userRepository->list($tag, $offset, $limit),
            context: ['groups' => $this->showGroups()]
        );
    }
}