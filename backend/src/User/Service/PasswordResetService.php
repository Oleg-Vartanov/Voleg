<?php

namespace App\User\Service;

use App\Core\Service\Mailer;
use App\User\Entity\User;
use App\User\Entity\UserToken;
use App\User\Enum\UserTokenTypeEnum;
use App\User\Repository\UserRepository;
use App\User\Repository\UserTokenRepository;
use Random\RandomException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

readonly class PasswordResetService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserService $userService,
        private UserTokenRepository $tokenRepository,
        private UserTokenService $tokenService,
        private Mailer $mailer,
        private ParameterBagInterface $parameterBag,
    ) {}


    /**
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function requestReset(User $user): void
    {
        // Remove older tokens.
        $this->tokenRepository->removeByUser($user, UserTokenTypeEnum::PASSWORD_RESET);

        [$token, $secret] = $this->tokenService->createToken(
            type: UserTokenTypeEnum::PASSWORD_RESET,
            user: $user,
        );

        $this->tokenRepository->save($token, true);

        $baseUrl = $this->parameterBag->get('client.url.auth-password-reset');
        $resetLink = $baseUrl . '?' . http_build_query([
            'selector' => $token->getSelector(),
            'secret' => $secret->plain,
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
        UserToken $token,
        string $secret,
        string $newPassword
    ): bool {
        if (
            $token->isExpired()
            || $token->getType() !== UserTokenTypeEnum::PASSWORD_RESET
            || !$this->tokenService->verifySecret($secret, $token->getSecret())
        ) {
            return false;
        }

        $user = $token->getUser();
        $this->userService->setHashedPassword($user, $newPassword);
        $this->tokenRepository->remove($token);
        $this->userRepository->save($user, true);

        return true;
    }
}