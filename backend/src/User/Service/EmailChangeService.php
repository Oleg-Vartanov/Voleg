<?php

namespace App\User\Service;

use App\Core\Service\Mailer;
use App\Core\Service\TokenHasher;
use App\User\Model\Entity\EmailChangeToken;
use App\User\Model\Entity\User;
use App\User\Repository\EmailChangeTokenRepository;
use App\User\Repository\UserRepository;
use DateMalformedStringException;
use DateTimeImmutable;
use InvalidArgumentException;
use LogicException;
use Random\RandomException;
use SensitiveParameter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

readonly class EmailChangeService
{
    public function __construct(
        private Mailer $mailer,
        private RouterInterface $router,
        private TokenHasher $hasher,
        private EmailChangeTokenRepository $tokenRepository,
        private UserRepository $userRepository,
        #[Autowire(param: 'email_change_token.ttl')]
        private int $tokenTtl,
    ) {
        if ($tokenTtl <= 0) {
            throw new InvalidArgumentException('EMAIL_CHANGE_TOKEN_TTL must be a positive number of seconds.');
        }
    }

    /**
     * Replaces any earlier email change request of the user.
     *
     * @throws TransportExceptionInterface|RandomException|DateMalformedStringException
     */
    public function requestEmailChange(User $user, string $newEmail): void
    {
        if ($user->getEmail() === $newEmail) {
            throw new LogicException('New email is the same as the current one.');
        }

        $secret = $this->hasher->generate();
        $this->tokenRepository->removeForUser($user);
        $this->tokenRepository->save(new EmailChangeToken(
            user: $user,
            tokenHash: $secret->hash,
            newEmail: $newEmail,
            expiresAt: new DateTimeImmutable(sprintf('+%d seconds', $this->tokenTtl)),
        ), true);

        $verifyLink = $this->router->generate('email_change_verify', [
            'token' => $secret->plain,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->mailer->send(
            template: 'email/emailChange.html.twig',
            to: $newEmail,
            subject: 'Verify Email Change',
            context: [
                'verifyLink' => $verifyLink,
                'username' => $user->getUsername(),
            ],
        );
    }

    /**
     * The token is deleted on any match, so a link works only once. Fails when
     * the new email was taken by another account after the request.
     */
    public function emailChange(#[SensitiveParameter] string $token): bool
    {
        if ($token === '') {
            return false;
        }

        $emailChangeToken = $this->tokenRepository->findByTokenHash($this->hasher->hash($token));
        if ($emailChangeToken === null) {
            return false;
        }

        $this->tokenRepository->remove($emailChangeToken);
        $newEmail = $emailChangeToken->getNewEmail();
        if ($emailChangeToken->isExpired() || $this->userRepository->findByEmail($newEmail) !== null) {
            $this->tokenRepository->flush();

            return false;
        }

        $user = $emailChangeToken->getUser();
        $user->setEmail($newEmail);
        $this->userRepository->save($user, true);

        return true;
    }
}
