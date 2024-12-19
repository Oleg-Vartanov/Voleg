<?php

namespace App\Controller;

use App\DTO\User\UpdateDto;
use App\DTO\User\UserDto;
use App\DTO\Validator\ValidationErrorResponse;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'User')]
#[Route('/users', name: 'user_')]
class UserController extends AbstractController
{
    /* OpenAi Documentation */
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Users',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: [User::SHOW]))
        )
    )]

    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(
        #[MapQueryParameter] ?string $displayName,
        UserRepository $userRepository
    ): JsonResponse {
        $filters = [];
        if ($displayName !== null) {
            $filters['displayName'] = $displayName;
        }

        $users = empty($filters) ? $userRepository->findAll() : $userRepository->findBy($filters);

        return $this->json($users, context: ['groups' => $this->showGroups()]);
    }

    /* OpenAi Documentation */
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'User',
        content: new Model(type: User::class, groups: User::SHOW_ALL)
    )]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'User Not Found')]

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id) ?? throw new NotFoundHttpException();

        return $this->json($user, context: ['groups' => $this->showGroups($id)]);
    }

    /* OpenAi Documentation */
    #[Security(name: 'Bearer')]
    #[OA\Response(response: Response::HTTP_NO_CONTENT, description: 'Deleted')]
    #[OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access Denied')]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'User Not Found')]

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response {
        $this->checkModifyAccess($id);
        $user = $userRepository->find($id) ?? throw new NotFoundHttpException();

        $entityManager->remove($user);
        $entityManager->flush();

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    /* OpenAi Documentation */
    #[Security(name: 'Bearer')]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'User Updated',
        content: new Model(type: User::class, groups: [User::SHOW_ALL])
    )]
    #[OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access Denied')]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'User Not Found')]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Validation errors',
        content: new Model(type: ValidationErrorResponse::class)
    )]

    #[Route('/{id}', name: 'patch', methods: ['PATCH'])]
    public function patch(
        int $id,
        #[MapRequestPayload(validationGroups: [UserDto::UPDATE])] UpdateDto $dto,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response {
        $this->checkModifyAccess($id);
        $user = $userRepository->find($id) ?? throw new NotFoundHttpException();

        $user->patch($dto);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json($user, context: ['groups' => $this->showGroups($id)]);
    }

    private function checkModifyAccess(int $id): void
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isOwner($id)) {
            throw new AccessDeniedHttpException();
        }
    }

    private function isOwner(int $userId): bool
    {
        return $this->getUser()?->getId() === $userId;
    }

    private function showGroups(?int $userIdToShow = null): array
    {
        $groups = [User::SHOW];
        if ($this->isGranted('ROLE_ADMIN')) {
            $groups[] = User::SHOW_ADMIN;
        }
        if (!is_null($userIdToShow) && $this->isOwner($userIdToShow)) {
            $groups[] = User::SHOW_OWNER;
        }

        return $groups;
    }
}