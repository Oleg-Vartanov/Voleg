<?php

namespace App\User\Http\V1;

use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\Core\Http\ApiController;
use App\Core\Service\AntiEnumerationLimiter;
use App\User\Http\V1\Request\PasswordResetDto;
use App\User\Repository\UserTokenRepository;
use App\User\Service\PasswordResetService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Post(
    security: [['Bearer' => []]],
    tags: ['User'],
    responses: [
        new MessageResponse(Response::HTTP_OK, PasswordResetAction::MESSAGE),
        new MessageResponse(Response::HTTP_FORBIDDEN, PasswordResetAction::MESSAGE_INVALID),
        new ValidationErrorResponse(),
    ],
)]
#[Route('/auth/password-reset', name: 'password_reset', methods: [Request::METHOD_POST])]
class PasswordResetAction extends ApiController
{
    public const string MESSAGE = 'Password reset was requested. Check your email.';
    public const string MESSAGE_INVALID = 'Invalid token.';

    public function __construct(
        private readonly UserTokenRepository $tokenRepository,
        private readonly PasswordResetService $service,
        private readonly AntiEnumerationLimiter $limiter,
    ) {
    }

    public function __invoke(
        #[MapRequestPayload] PasswordResetDto $dto
    ): Response {
        if ($this->limiter->limit('passwordReset' . $dto->selector)) {
            return $this->limitResponse();
        }

        $token = $this->tokenRepository->findBySelector($dto->selector);

        if ($token === null) {
            return $this->messageResponse(self::MESSAGE_INVALID, Response::HTTP_FORBIDDEN);
        }

        $result = $this->service->resetPassword(
            token: $token,
            secret: $dto->secret,
            newPassword: $dto->password,
        );

        if ($result === false) {
            return $this->messageResponse(self::MESSAGE_INVALID, Response::HTTP_FORBIDDEN);
        }

        return $this->messageResponse(self::MESSAGE);
    }
}
