<?php

namespace App\User\Controller;

use App\User\Controller\Trait\UserControllerTrait;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'User')]
#[Security(name: 'Bearer')]
#[OA\Response(response: Response::HTTP_NO_CONTENT, description: 'Deleted')]
#[OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access denied')]
#[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'User not found')]

#[Route('/users/{id}', name: 'user_delete', methods: [Request::METHOD_DELETE])]
class UserDeleteAction extends AbstractController
{
    use UserControllerTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(int $id): Response
    {
        $this->checkModifyAccess($id);
        $user = $this->userRepository->find($id);

        if ($user === null) {
            throw new NotFoundHttpException();
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
