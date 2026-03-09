<?php

namespace App\User\Http\V1;

use App\Core\Documentation\Attribute\Response\AccessDeniedResponse;
use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\Core\Http\ApiController;
use App\User\Http\V1\Trait\UserControllerTrait;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Delete(
    security: [['Bearer' => []]],
    tags: ['User'],
    responses: [
        new MessageResponse(Response::HTTP_NO_CONTENT, 'Deleted'),
        new AccessDeniedResponse(),
        new NotFoundResponse('User not found'),
    ]
)]
#[Route('/users/{id}', name: 'user_delete', methods: [Request::METHOD_DELETE])]
class UserDeleteAction extends ApiController
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

        return $this->messageResponse('Deleted', Response::HTTP_NO_CONTENT);
    }
}
