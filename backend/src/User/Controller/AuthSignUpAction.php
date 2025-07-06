<?php

namespace App\User\Controller;

use App\Core\DTO\Documentation\Validator\ValidationErrorResponse;
use App\User\DTO\Request\SignUpDto;
use App\User\DTO\Request\UserDto;
use App\User\Service\AuthService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Authorization')]
#[OA\Response(response: Response::HTTP_CREATED, description: 'Sign up successful')]
#[OA\Response(
    response: Response::HTTP_UNPROCESSABLE_ENTITY,
    description: 'Validation errors',
    content: new Model(type: ValidationErrorResponse::class)
)]

#[Route('/auth/sign-up', name: 'sign_up', methods: [Request::METHOD_POST], format: 'json')]
class AuthSignUpAction extends AbstractController
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function __invoke(
        #[MapRequestPayload(validationGroups: [UserDto::SIGN_UP])] SignUpDto $dto,
    ): JsonResponse {
        $this->authService->signUp($dto);

        return $this->json([
            'message' => 'User was created. Now you need to verify it via email.'
        ], Response::HTTP_CREATED);
    }
}