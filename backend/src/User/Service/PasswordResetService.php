<?php

namespace App\User\Service;

use App\Core\Service\Mailer;
use App\Core\Service\TokenHasher;
use App\User\Model\Entity\PasswordResetToken;
use App\User\Model\Entity\User;
use App\User\Repository\PasswordResetTokenRepository;
use App\User\Repository\UserRepository;
use DateMalformedStringException;
use DateTimeImmutable;
use InvalidArgumentException;
use Random\RandomException;
use SensitiveParameter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

readonly class PasswordResetService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserService $userService,
        private TokenHasher $hasher,
        private PasswordResetTokenRepository $tokenRepository,
        private RefreshTokenService $refreshTokens,
        private Mailer $mailer,
        private ParameterBagInterface $parameterBag,
        #[Autowire(param: 'password_reset_token.ttl')]
        private int $tokenTtl,
    ) {
        if ($tokenTtl <= 0) {
            throw new InvalidArgumentException('PASSWORD_RESET_TOKEN_TTL must be a positive number of seconds.');
        }
    }

    /**
     * Replaces any earlier reset request of the user.
     *
     * @throws RandomException|TransportExceptionInterface|DateMalformedStringException
     */
    public function requestReset(User $user): void
    {
        $secret = $this->hasher->generate();
        $this->tokenRepository->removeForUser($user);
        $this->tokenRepository->save(new PasswordResetToken(
            user: $user,
            tokenHash: $secret->hash,
            expiresAt: new DateTimeImmutable(sprintf('+%d seconds', $this->tokenTtl)),
        ), true);

        $baseUrl = $this->parameterBag->get('client.url.auth-password-reset');
        $resetLink = $baseUrl . '?' . http_build_query([
            'token' => $secret->plain,
        ]);

        $this->mailer->send(
            template: 'email/passwordReset.html.twig',
            to: $user->getEmail(),
            subject: 'Password Reset Request',
            context: [
                'resetLink' => $resetLink,
                'username' => $user->getUsername(),
            ]
        );
    }

    /**
     * The token is deleted on any match, so a link works only once. Signs the
     * user out everywhere by revoking all refresh tokens.
     */
    public function resetPassword(
        #[SensitiveParameter] string $token,
        #[SensitiveParameter] string $newPassword,
    ): bool {
        $resetToken = $this->tokenRepository->findByTokenHash($this->hasher->hash($token));
        if ($resetToken === null) {
            return false;
        }

        $this->tokenRepository->remove($resetToken);
        if ($resetToken->isExpired()) {
            $this->tokenRepository->flush();

            return false;
        }

        $user = $resetToken->getUser();
        $this->userService->setHashedPassword($user, $newPassword);
        $this->refreshTokens->revokeAll($user);
        $this->userRepository->save($user, true);

        return true;
    }
}
