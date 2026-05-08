<?php

namespace App\User\Http\V1;

use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\UnauthorizedResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\Core\Http\ApiController;
use App\Core\Service\AntiEnumerationLimiter;
use App\Core\ValueObject\Validator\Violation;
use App\User\Entity\User;
use App\User\Http\V1\Request\PasswordChangeDto;
use App\User\Repository\UserRepository;
use App\User\Service\UserService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Post(
    security: [['Bearer' => []]],
    tags: ['User'],
    responses: [
        new MessageResponse(Response::HTTP_OK, PasswordChangeAction::MESSAGE),
        new ValidationErrorResponse(),
        new UnauthorizedResponse(),
    ],
)]
#[Route('/auth/password-change', name: 'password_change', methods: [Request::METHOD_POST])]
class PasswordChangeAction extends ApiController
{
    public const string MESSAGE = 'Password changed';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserService $userService,
        private readonly AntiEnumerationLimiter $limiter,
    ) {
    }

    public function __invoke(
        #[CurrentUser] User $user,
        #[MapRequestPayload] PasswordChangeDto $dto
    ): Response {
        if ($this->limiter->limit('passwordChange' . $user->getId())) {
            return $this->limitResponse();
        }

        if (!$this->userService->isPasswordValid($user, $dto->currentPassword)) {
            return $this->validationErrorResponse(
                new Violation('currentPassword', 'Invalid current password')
            );
        }

        if ($dto->newPassword === $dto->currentPassword) {
            return $this->validationErrorResponse(
                new Violation('newPassword', 'New password must be different from the current password')
            );
        }

        $this->userService->setHashedPassword($user, $dto->newPassword);
        $this->userRepository->save($user, true);

        return $this->messageResponse(self::MESSAGE);
    }
}
