<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Core\Validator\Constraints;

use App\Core\Interface\PropertyAccessorInterface;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InitializedAndNotNullValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof InitializedAndNotNull) {
            throw new UnexpectedTypeException($constraint, InitializedAndNotNull::class);
        }

        $property = $this->context->getPropertyName();
        if (null === $property) {
            throw new InvalidArgumentException('Cannot access property name');
        }

        $object = $this->context->getObject();
        if (!$object instanceof PropertyAccessorInterface) {
            throw new InvalidArgumentException('Object should implement an interface');
        }

        if ($object->isPropertyInitialized($property) && null === $value) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->addViolation();
        }
    }
}
