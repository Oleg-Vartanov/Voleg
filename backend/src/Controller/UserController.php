<?php

namespace App\Controller;

use App\DTO\UserDto;
use App\Enum\Roles;
use App\Repository\UserRepository;
use App\Service\AttributeReader;
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
    public function list(AttributeReader $attributeReader): JsonResponse
    {
        $roles = $this->getUser()?->getRoles() ?? [];
        $users = $this->userRepository->findAll();

        $data = [];
        foreach ($users as $user) {
            $data[] = UserDto::createByUser($user);
        }

        return $this->json($data, context: [
            'groups' => ['GET'],
            'ignored_attributes' => $attributeReader->getDisallowedProperties(UserDto::class, $roles),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, AttributeReader $attributeReader): JsonResponse
    {
        $user = $this->userRepository->find($id) ?? throw new NotFoundHttpException();

        $roles = [];
        if ($currentUser = $this->getUser()) {
            $roles = $currentUser->getRoles();
            if ($currentUser->getId() === $id) {
                $roles[] = Roles::OWNER->value;
            }
        }

        return $this->json(
            UserDto::createByUser($user),
            context: [
                'groups' => ['GET'],
                'ignored_attributes' => $attributeReader->getDisallowedProperties(UserDto::class, $roles),
            ]
        );
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $this->canModifyOr403($id);

        $user = $this->userRepository->find($id) ?? throw new NotFoundHttpException();

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new Response(Response::HTTP_NO_CONTENT);
    }

    /** @throws ReflectionException */
    #[Route('/{id}', name: 'patch', methods: ['PATCH'])]
    public function patch(int $id, Request $request, AttributeReader $attributeReader): Response
    {
        $this->canModifyOr403($id);

        $user = $this->userRepository->find($id) ?? throw new NotFoundHttpException();

        $dto = UserDto::createByArray($request->getPayload()->all());
        $dto = $this->serializer->normalize($dto, context: ['groups' => ['PATCH']]);

        if ($response = $this->validationErrorResponse($dto)) {
            return $response;
        }

        $user->patch($dto);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new Response(Response::HTTP_OK);
    }

    private function canModifyOr403(int $id): void
    {
        $user = $this->getUser();
        if (!$user->hasRoles(['ROLE_ADMIN']) && $user->getId() != $id) {
            throw new AccessDeniedHttpException();
        }
    }
}