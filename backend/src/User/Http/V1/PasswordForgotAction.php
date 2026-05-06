<?php

namespace App\User\Http\V1;

use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\Core\Http\ApiController;
use App\User\Http\V1\Request\PasswordForgotDto;
use App\User\Repository\UserRepository;
use App\User\Service\PasswordResetService;
use OpenApi\Attributes as OA;
use Random\RandomException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Post(
    security: [['Bearer' => []]],
    tags: ['User'],
    responses: [
        new MessageResponse(Response::HTTP_OK, PasswordForgotAction::MESSAGE),
        new ValidationErrorResponse(),
    ],
)]
#[Route('/auth/password-forgot', name: 'password_forgot', methods: [Request::METHOD_POST])]
class PasswordForgotAction extends ApiController
{
    public const string MESSAGE = 'Password reset was requested. Check your email.';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PasswordResetService $passwordResetService,
    ) {
    }

    /**
     * @throws TransportExceptionInterface|RandomException
     */
    public function __invoke(
        #[MapRequestPayload] PasswordForgotDto $dto
    ): Response {
        $user = $this->userRepository->findByEmail($dto->email);
        if ($user !== null) {
            $this->passwordResetService->requestReset($user);
        }

        return $this->messageResponse(self::MESSAGE);
    }
}
