<?php

namespace App\Validator\Constraints;

use App\Interface\PropertyAccessorInterface;
use InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UserRolesValidator extends ConstraintValidator
{
    public function __construct(private readonly Security $security)
    {
    }

    /** @inheritDoc */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UserRoles) {
            throw new UnexpectedTypeException($constraint, UserRoles::class);
        }

        $property = $this->context->getPropertyName();
        $object = $this->context->getObject();

        if (!$object instanceof PropertyAccessorInterface) {
            throw new InvalidArgumentException('Object should implement an interface');
        }

        if (!$object->isPropertyInitialized($property)) {
            return;
        }

        if ($user = $this->security->getUser()) {
            if ($this->security->isGranted($constraint->roles)) {
                return;
            }

            if ($method = $constraint->allowedUserIdentifierGetter) {
                if (!method_exists($object, $method)) {
                    throw new InvalidArgumentException('Allowed user identifier property name "'.$method.'" does not exist');
                }

                if ($object->$method() == $user->getUserIdentifier()) {
                    return;
                }
            }
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ roles }}', implode(', ', $constraint->roles))
            ->addViolation();
    }
}