<?php

namespace App\Validator\Constraints;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class EqualParamConfigValidator extends ConstraintValidator
{
    public function __construct(private readonly ParameterBagInterface $parameterBag)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!$constraint instanceof EqualParamConfig) {
            throw new UnexpectedTypeException($constraint, EqualParamConfig::class);
        }

        $param = $this->parameterBag->get($constraint->name);

        if ($value !== $param) {
            $this->context->buildViolation($constraint->message)
                          ->addViolation();
        }
    }
}
