<?php

namespace App\User\Http\V1\Auth;

use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\Core\Http\ApiController;
use App\User\Http\V1\User\UserRequestPayload;
use App\User\Service\AuthService;
use DateMalformedStringException;
use OpenApi\Attributes as OA;
use Random\RandomException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Post(
    tags: ['User: Auth'],
    responses: [
        new MessageResponse(
            Response::HTTP_CREATED,
            'Sign up successful',
            SignUpAction::MESSAGE,
        ),
        new ValidationErrorResponse(),
    ],
)]
#[Route('/auth/sign-up', name: 'sign_up', methods: [Request::METHOD_POST], format: 'json')]
class SignUpAction extends ApiController
{
    private const string MESSAGE = 'User was created. Now you need to verify it via email.';

    public function __construct(private readonly AuthService $authService)
    {
    }

    /**
     * @throws TransportExceptionInterface|RandomException|DateMalformedStringException
     */
    public function __invoke(
        #[MapRequestPayload(validationGroups: [UserRequestPayload::SIGN_UP])] SignUpRequestPayload $payload,
    ): JsonResponse {
        $this->authService->signUp($payload);

        return $this->messageResponse(
            self::MESSAGE,
            Response::HTTP_CREATED,
        );
    }
}
