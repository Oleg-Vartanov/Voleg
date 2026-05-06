<?php

namespace App\User\Service;

use App\Core\Service\Mailer;
use App\Core\Util\PropertyAccessor;
use App\User\Entity\User;
use App\User\Http\V1\Request\UserDto;
use App\User\Repository\UserRepository;
use LogicException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

readonly class UserService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository,
        private Mailer $mailer,
        private RouterInterface $router,
    ) {
    }

    /**
     * @param array<string> $roles
     */
    public function create(
        string $email,
        string $plaintextPassword,
        string $displayName,
        string $tag,
        array $roles = [],
    ): User {
        $user = new User();

        $user->setEmail($email);
        $this->setHashedPassword($user, $plaintextPassword);
        $user->setDisplayName($displayName);
        $user->setTag($tag);
        $user->setRoles($roles);

        return $user;
    }

    public function createByDto(UserDto $dto): User
    {
        return $this->create(
            email: $dto->email,
            plaintextPassword: $dto->password,
            displayName: $dto->displayName,
            tag: $dto->tag,
        );
    }

    public function patch(User $user, UserDto $dto): User
    {
        $props = array_flip(PropertyAccessor::getInitializedProperties($dto));

        if (isset($props['email']) && $dto->email !== $user->getEmail()) {
            $this->requestEmailChange($user, $dto->email);
        }
        if (isset($props['displayName'])) {
            $user->setDisplayName($dto->displayName);
        }
        if (isset($props['tag'])) {
            $user->setTag($dto->tag);
        }

        return $user;
    }

    public function isPasswordValid(User $user, string $plainPassword): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $plainPassword);
    }

    public function setHashedPassword(User $user, string $plaintextPassword): void
    {
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plaintextPassword);
        $user->setPassword($hashedPassword);
    }

    public function verifyEmailChange(string $code, User $user): bool
    {
        $isValidCode = !$user->emailChangeCodeExpired() && $user->getEmailChangeCode() === $code;
        $emailChange = $user->getEmailChange();
        $verified = $isValidCode && $emailChange !== null;

        if ($verified) {
            $user->setEmail($emailChange);
            $user->clearEmailChangeChange();
            $this->userRepository->save($user, true);
        }

        return $verified;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function requestEmailChange(User $user, string $emailChange): void
    {
        if ($user->getEmail() === $emailChange) {
            throw new LogicException('New email is the same as the current one.');
        }

        $user->setEmailChange($emailChange);
        $user->updateEmailChangeCode();
        $this->userRepository->save($user, true);

        $this->sendEmailChangeVerificationEmail($user);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmailChangeVerificationEmail(User $user): void
    {
        $emailChange = $user->getEmailChange();
        if ($emailChange === null || $user->getEmailChangeCode() === null) {
            throw new LogicException('Pending email change is not initialized.');
        }

        $this->mailer->send(
            template: 'email/email-change.html.twig',
            to: $emailChange,
            subject: 'Verify Email Change',
            context: [
                'verifyLink' => $this->createEmailChangeVerificationLink($user),
                'displayName' => $user->getDisplayName(),
            ],
        );
    }

    private function createEmailChangeVerificationLink(User $user): string
    {
        return $this->router->generate('email_change_verify', [
            'userId' => $user->getId(),
            'code' => $user->getEmailChangeCode(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
