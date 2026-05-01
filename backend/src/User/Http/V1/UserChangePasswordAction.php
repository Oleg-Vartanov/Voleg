<?php

namespace App\User\Http\V1;

use App\Core\Documentation\Attribute\Response\MessageResponse;
use App\Core\Documentation\Attribute\Response\UnauthorizedResponse;
use App\Core\Documentation\Attribute\Response\ValidationErrorResponse;
use App\Core\Http\ApiController;
use App\Core\ValueObject\Validator\Violation;
use App\User\Entity\User;
use App\User\Http\V1\Request\PasswordChangeDto;
use App\User\Repository\UserRepository;
use App\User\Service\UserService;
use OpenApi\Attributes as OA;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Post(
    security: [['Bearer' => []]],
    tags: ['User'],
    responses: [
        new MessageResponse(Response::HTTP_OK, 'Password changed'),
        new ValidationErrorResponse(),
        new UnauthorizedResponse(),
    ],
)]
#[Route('/users/change-password', name: 'user_change_password', methods: [Request::METHOD_POST])]
class UserChangePasswordAction extends ApiController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserService $userService,
        #[Target('change_password')]
        private readonly RateLimiterFactoryInterface $changePasswordLimiter,
    ) {
    }

    public function __invoke(
        #[CurrentUser] User $user,
        #[MapRequestPayload] PasswordChangeDto $dto
    ): Response {
        $limiter = $this->changePasswordLimiter->create((string) $user->getId());
        $limit = $limiter->consume();
        if (!$limit->isAccepted()) {
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

        return $this->messageResponse('Password changed');
    }
}
