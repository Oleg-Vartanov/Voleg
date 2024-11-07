<?php

namespace App\Controller;

use App\DTO\User\UpdateDto;
use App\DTO\User\UserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use ReflectionException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function list(): JsonResponse
    {
        $users = $this->userRepository->findAll();

        $groups = [User::SHOW];
        if ($this->isGranted('ROLE_ADMIN')) {
            $groups[] = User::SHOW_ADMIN;
        }

        return $this->json($users, context: ['groups' => $groups]);
    }

    /* OpenAi Documentation */
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'User',
        content: new Model(type: User::class, groups: [User::SHOW, User::SHOW_ADMIN, User::SHOW_OWNER])
    )]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'User Not Found')]

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id) ?? throw new NotFoundHttpException();

        $groups = [User::SHOW];
        if ($this->isGranted('ROLE_ADMIN')) {
            $groups[] = User::SHOW_ADMIN;
        }
        if ($this->getUser()?->getId() === $id) {
            $groups[] = User::SHOW_OWNER;
        }

        return $this->json($user, context: ['groups' => $groups]);
    }

    /* OpenAi Documentation */
    #[OA\Response(response: Response::HTTP_NO_CONTENT, description: 'Deleted')]
    #[OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access Denied')]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'User Not Found')]

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $isOwner =  $this->getUser()?->getId() === $id;
        if (!$isOwner && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->userRepository->find($id) ?? throw new NotFoundHttpException();

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    /* OpenAi Documentation */
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'User Updated',
        content: new Model(type: UpdateDto::class, groups: [UserDto::UPDATE_ADMIN, UserDto::UPDATE_OWNER])
    )]
    #[OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Access Denied')]
    #[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'User Not Found')]

    /** @throws ReflectionException */
    #[Route('/{id}', name: 'patch', methods: ['PATCH'])]
    public function patch(int $id, Request $request): Response
    {
        $groups = [];
        if ($this->isGranted('ROLE_ADMIN')) {
            $groups = array_merge($groups, [User::SHOW_ADMIN, UserDto::UPDATE_ADMIN]);
        }
        if ($this->getUser()?->getId() === $id) {
            $groups = array_merge($groups, [User::SHOW_OWNER, UserDto::UPDATE_OWNER]);
        }

        if (empty($groups)) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->userRepository->find($id) ?? throw new NotFoundHttpException();

        $dto = $this->serializer->denormalize(
            $request->getPayload()->all(),
            UpdateDto::class,
            context: ['groups' => $groups],
        );

        if ($response = $this->validationErrorResponse($dto, $groups)) {
            return $response;
        }

        $user->patch($dto);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json($user, context: ['groups' => array_merge($groups, [User::SHOW])]);
    }
}