<?php

namespace App\User\Service;

use App\Core\Util\PropertyAccessor;
use App\User\Entity\User;
use App\User\Http\V1\Request\UserDto;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

readonly class UserService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private ParameterBagInterface $parameterBag,
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
        $user->setPassword($this->passwordHasher->hashPassword($user, $plaintextPassword));
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

    public function verifyEmailChange(string $code, User $user): bool
    {
        $isValidCode = !$user->emailChangeCodeExpired() && $user->getEmailChangeCode() === $code;
        $emailChange = $user->getEmailChange();
        $verified = $isValidCode && $emailChange !== null;

        if ($verified) {
            $user->setEmail($emailChange);
            $user->clearEmailChangeChange();
            $this->entityManager->persist($user);
            $this->entityManager->flush();
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
        $this->entityManager->persist($user);
        $this->entityManager->flush();

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

        $email = new TemplatedEmail()
            ->from('no-reply@' . $this->parameterBag->get('app.mail.domain'))
            ->to(new Address($emailChange))
            ->subject('Verify Email Change')
            ->htmlTemplate('email/email-change.html.twig')
            ->context([
                'verifyLink' => $this->createEmailChangeVerificationLink($user),
                'displayName' => $user->getDisplayName(),
                'supportEmail' => $this->parameterBag->get('app.support.email'),
            ])
        ;

        $this->mailer->send($email);
    }

    private function createEmailChangeVerificationLink(User $user): string
    {
        return $this->router->generate('email_change_verify', [
            'userId' => $user->getId(),
            'code' => $user->getEmailChangeCode(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
