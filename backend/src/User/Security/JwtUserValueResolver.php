<?php

namespace App\User\Security;

use App\User\Model\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Controller\UserValueResolver;

/**
 * The firewall user is a {@see JwtUser} built from the token. Controllers that
 * type-hint the Doctrine user receive a reference, which does not query until
 * a field other than the id is read.
 */
#[AsDecorator(decorates: 'security.user_value_resolver')]
readonly class JwtUserValueResolver implements ValueResolverInterface
{
    public function __construct(
        #[AutowireDecorated]
        private UserValueResolver $inner,
        private TokenStorageInterface $tokenStorage,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        if (
            $user instanceof JwtUser
            && $argument->getType() === User::class
            && $argument->getAttributesOfType(CurrentUser::class, ArgumentMetadata::IS_INSTANCEOF) !== []
        ) {
            $reference = $this->entityManager->getReference(User::class, $user->getId());
            if (!$reference instanceof User) {
                throw new AccessDeniedException('Authenticated user was not found.');
            }

            return [$reference];
        }

        return $this->inner->resolve($request, $argument);
    }
}
