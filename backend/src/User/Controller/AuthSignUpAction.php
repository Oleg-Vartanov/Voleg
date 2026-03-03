<?php

namespace App\User\Controller;

use App\Core\Controller\ApiController;
use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\User\DTO\Request\SignUpDto;
use App\User\DTO\Request\UserDto;
use App\User\Service\AuthService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Post(
    tags: ['Authorization'],
    responses: [
        new MessageResponse(
            Response::HTTP_CREATED,
            'Sign up successful',
            AuthSignUpAction::MESSAGE,
        ),
        new ValidationErrorResponse(),
    ],
)]
#[Route('/auth/sign-up', name: 'sign_up', methods: [Request::METHOD_POST], format: 'json')]
class AuthSignUpAction extends ApiController
{
    private const string MESSAGE = 'User was created. Now you need to verify it via email.';

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

        return $this->messageResponse(
            self::MESSAGE,
            Response::HTTP_CREATED,
        );
    }
}
