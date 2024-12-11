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
use ReflectionException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag(name: 'User')]
#[Route('/users', name: 'user_')]
class UserController extends ApiController
{
    public function __construct(
        protected ValidatorInterface $validator,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly SerializerInterface $serializer,
    ) {
    }

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
    public function list(#[MapQueryParameter] ?string $displayName): JsonResponse
    {
        $filters = [];
        if ($displayName !== null) {
            $filters['displayName'] = $displayName;
        }

        $users = empty($filters) ? $this->userRepository->findAll() : $this->userRepository->findBy($filters);

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
    public function show(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id) ?? throw new NotFoundHttpException();

        return $this->json($user, context: ['groups' => $this->showGroups($id)]);
    }

    /* OpenAi Documentation */
    #[Security(name: 'Bearer')]
    #[OA\Response(response: Response::HTTP_NO_CONTENT, description: 'Deleted')]
    #[OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access Denied')]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'User Not Found')]

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $this->checkModifyAccess($id);

        $user = $this->userRepository->find($id) ?? throw new NotFoundHttpException();

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    /* OpenAi Documentation */
    #[Security(name: 'Bearer')]
    #[OA\RequestBody(content: new Model(type: UpdateDto::class, groups: [UserDto::UPDATE]))]
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
        content: new Model(type: ValidationErrorResponse::class))
    ]

    /** @throws ReflectionException */
    #[Route('/{id}', name: 'patch', methods: ['PATCH'])]
    public function patch(int $id, Request $request): Response
    {
        $this->checkModifyAccess($id);

        $user = $this->userRepository->find($id) ?? throw new NotFoundHttpException();

        /** @var UpdateDto $dto */
        $dto = $this->serializer->denormalize(
            $request->getPayload()->all(), UpdateDto::class, context: ['groups' => [UserDto::UPDATE]],
        );

        if ($this->isOwner($id)) {
            $dto->setOwnerIdentifier($this->getUser()->getUserIdentifier());
        }

        if ($response = $this->validationErrorResponse($dto, [UserDto::UPDATE])) {
            return $response;
        }

        $user->patch($dto);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

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