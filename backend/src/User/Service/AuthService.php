<?php

namespace App\User\Service;

use App\Core\Service\Mailer;
use App\Core\Service\TokenHasher;
use App\User\Model\Entity\User;
use App\User\Model\Entity\VerificationToken;
use App\User\Http\V1\Auth\SignUpRequestPayload;
use App\User\Repository\UserRepository;
use App\User\Repository\VerificationTokenRepository;
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

readonly class AuthService
{
    public function __construct(
        private Mailer $mailer,
        private RouterInterface $router,
        private UserService $userService,
        private UserRepository $userRepository,
        private TokenHasher $hasher,
        private VerificationTokenRepository $tokenRepository,
        #[Autowire(param: 'verification_token.ttl')]
        private int $tokenTtl,
    ) {
        if ($tokenTtl <= 0) {
            throw new InvalidArgumentException('VERIFICATION_TOKEN_TTL must be a positive number of seconds.');
        }
    }

    /** @throws TransportExceptionInterface|RandomException|DateMalformedStringException */
    public function signUp(SignUpRequestPayload $payload): void
    {
        $user = $this->userService->createByPayload($payload);
        $this->userRepository->save($user, true);
        $this->sendVerificationEmail($user);
    }

    /**
     * The token is deleted on any match, so a link works only once.
     */
    public function verifyUser(#[SensitiveParameter] string $token): bool
    {
        if ($token === '') {
            return false;
        }

        $verificationToken = $this->tokenRepository->findByTokenHash($this->hasher->hash($token));
        if ($verificationToken === null) {
            return false;
        }

        $this->tokenRepository->remove($verificationToken);
        if ($verificationToken->isExpired()) {
            $this->tokenRepository->flush();

            return false;
        }

        $user = $verificationToken->getUser();
        $user->setVerified(true);
        $this->userRepository->save($user, true);

        return true;
    }

    /**
     * Replaces any earlier verification token of the user.
     *
     * @throws TransportExceptionInterface|RandomException|DateMalformedStringException
     */
    public function sendVerificationEmail(User $user): void
    {
        if ($user->isVerified()) {
            throw new LogicException('User is already verified.');
        }

        $secret = $this->hasher->generate();
        $this->tokenRepository->removeForUser($user);
        $this->tokenRepository->save(new VerificationToken(
            user: $user,
            tokenHash: $secret->hash,
            expiresAt: new DateTimeImmutable(sprintf('+%d seconds', $this->tokenTtl)),
        ), true);

        $verifyLink = $this->router->generate('sign_up_verify', [
            'token' => $secret->plain,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->mailer->send(
            template: 'email/signUp.html.twig',
            to: $user->getEmail(),
            subject: 'Verify Sign Up',
            context: [
                'verifyLink' => $verifyLink,
                'username' => $user->getUsername(),
            ],
        );
    }
}
