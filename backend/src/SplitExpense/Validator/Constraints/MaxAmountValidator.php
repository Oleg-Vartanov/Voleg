<?php

namespace App\SplitExpense\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class MaxAmountValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof MaxAmount) {
            throw new UnexpectedTypeException($constraint, MaxAmount::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!is_int($value) && !is_numeric($value)) {
            throw new UnexpectedValueException($value, 'int');
        }

        if (abs((int) $value) > MaxAmount::MAX) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', (string) $value)
                ->setParameter('{{ compared_value }}', (string) MaxAmount::MAX)
                ->addViolation();
        }
    }
}
