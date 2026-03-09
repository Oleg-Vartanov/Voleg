<?php

namespace App\User\Http\V1;

use App\Core\Documentation\Attribute\Response\AccessDeniedResponse;
use App\Core\Documentation\Attribute\Response\NotFoundResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\Core\Http\ApiController;
use App\User\Entity\User;
use App\User\Http\V1\Request\UpdateDto;
use App\User\Http\V1\Request\UserDto;
use App\User\Http\V1\Trait\UserControllerTrait;
use App\User\Repository\UserRepository;
use App\User\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Patch(
    security: [['Bearer' => []]],
    tags: ['User'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'User Updated',
            content: new Model(type: User::class, groups: User::SHOW_ALL),
        ),
        new AccessDeniedResponse(),
        new NotFoundResponse('User not found'),
        new ValidationErrorResponse(),
    ],
)]
#[Route('/users/{id}', name: 'user_patch', methods: [Request::METHOD_PATCH])]
class UserPatchAction extends ApiController
{
    use UserControllerTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserService $userService,
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

        $user = $this->userService->patch($user, $dto);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json($user, context: ['groups' => $this->showGroups($id)]);
    }
}
