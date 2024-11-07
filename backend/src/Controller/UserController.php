<?php

namespace App\Controller;

use App\DTO\UserDto;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $users = $this->userRepository->findAll();

        $groups = ['show'];
        if ($this->isGranted('ROLE_ADMIN')) {
            $groups[] = 'show:admin';
        }

        return $this->json($users, context: ['groups' => $groups]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id) ?? throw new NotFoundHttpException();

        $groups = ['show'];
        if ($this->isGranted('ROLE_ADMIN')) {
            $groups[] = 'show:admin';
        }
        if ($this->getUser()?->getId() === $id) {
            $groups[] = 'show:owner';
        }

        return $this->json($user, context: ['groups' => $groups]);
    }

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

    /** @throws ReflectionException */
    #[Route('/{id}', name: 'patch', methods: ['PATCH'])]
    public function patch(int $id, Request $request): Response
    {
        $groups = [];
        if ($this->isGranted('ROLE_ADMIN')) {
            $groups = array_merge($groups, ['show:admin', 'edit:admin']);
        }
        if ($this->getUser()?->getId() === $id) {
            $groups = array_merge($groups, ['show:owner', 'edit:owner']);
        }

        if (empty($groups)) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->userRepository->find($id) ?? throw new NotFoundHttpException();

        $dto = $this->serializer->denormalize(
            $request->getPayload()->all(),
            UserDto::class,
            context: ['groups' => $groups],
        );

        if ($response = $this->validationErrorResponse($dto, $groups)) {
            return $response;
        }

        $user->patch($dto);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json($user, context: ['groups' => array_merge($groups, ['show'])]);
    }
}