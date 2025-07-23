<?php

namespace App\User\Controller;

use App\Core\DTO\Documentation\Validator\ValidationErrorResponse;
use App\User\Controller\Trait\UserControllerTrait;
use App\User\DTO\Request\UpdateDto;
use App\User\DTO\Request\UserDto;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'User')]
#[Security(name: 'Bearer')]
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'User Updated',
    content: new Model(type: User::class, groups: [User::SHOW_ALL])
)]
#[OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access denied')]
#[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'User not found')]
#[OA\Response(
    response: Response::HTTP_UNPROCESSABLE_ENTITY,
    description: 'Validation errors',
    content: new Model(type: ValidationErrorResponse::class)
)]

#[Route('/users/{id}', name: 'user_patch', methods: [Request::METHOD_PATCH])]
class UserPatchAction extends AbstractController
{
    use UserControllerTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(
        int $id,
        #[MapRequestPayload(validationGroups: [UserDto::UPDATE])] UpdateDto $dto,
    ): Response {
        $this->checkModifyAccess($id);
        $user = $this->userRepository->find($id);

        if ($user === null) {
            throw new NotFoundHttpException();
        }

        $user->patch($dto);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json($user, context: ['groups' => $this->showGroups($id)]);
    }
}
