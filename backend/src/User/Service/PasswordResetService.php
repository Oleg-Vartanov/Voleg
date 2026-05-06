<?php

namespace App\User\Service;

use App\Core\Service\Mailer;
use App\Core\Service\TokenService;
use App\User\Entity\User;
use App\User\Entity\UserPasswordReset;
use App\User\Repository\UserPasswordResetRepository;
use App\User\Repository\UserRepository;
use DateTimeImmutable;
use Random\RandomException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

readonly class PasswordResetService
{
    public const string TOKEN_EXPIRE_TIME = '+30 minutes';

    public function __construct(
        private UserRepository $userRepository,
        private UserService $userService,
        private UserPasswordResetRepository $repository,
        private TokenService $tokenService,
        private Mailer $mailer,
        private ParameterBagInterface $parameterBag,
    ) {}


    /**
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function requestReset(User $user): void
    {
        $this->repository->removeByUser($user); // Remove older tokens.

        $token = $this->tokenService->generate();
        $passwordReset = new UserPasswordReset(
            user: $user,
            tokenHash: $token->hash,
            expiresAt: new DateTimeImmutable(self::TOKEN_EXPIRE_TIME),
        );

        $this->repository->save($passwordReset, true);

        $baseUrl = $this->parameterBag->get('client.url.auth-password-reset');
        $resetLink = $baseUrl . '?' . http_build_query([
            'selector' => $passwordReset->getSelector(),
            'token' => $token->plain,
        ]);

        $this->mailer->send(
            template: 'email/password-reset.html.twig',
            to: $user->getEmail(),
            subject: 'Password Reset Request',
            context: [
                'resetLink' => $resetLink,
                'displayName' => $user->getDisplayName(),
            ]
        );
    }

    public function resetPassword(
        UserPasswordReset $passwordReset,
        string $plainToken,
        string $newPassword
    ): bool {
        if (
            $passwordReset->isExpired()
            || !$this->tokenService->verify($plainToken, $passwordReset->getTokenHash())
        ) {
            return false;
        }

        $user = $passwordReset->getUser();
        $this->userService->setHashedPassword($user, $newPassword);
        $this->repository->remove($passwordReset);
        $this->userRepository->save($user, true);

        return true;
    }
}