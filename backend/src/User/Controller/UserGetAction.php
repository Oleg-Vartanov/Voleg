<?php

namespace App\User\Controller;

use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\User\Controller\Trait\UserControllerTrait;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Get(
    tags: ['User'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'User',
            content: new Model(type: User::class, groups: User::SHOW_ALL),
        ),
        new NotFoundResponse('User not found'),
    ],
)]
#[Route('/users/{id}', name: 'user_get', methods: [Request::METHOD_GET])]
class UserGetAction extends AbstractController
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
