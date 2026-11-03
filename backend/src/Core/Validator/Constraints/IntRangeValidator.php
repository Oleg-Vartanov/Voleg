<?php

namespace App\Core\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IntRangeValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof IntRange) {
            return;
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!is_int($value) && !is_numeric($value)) {
            return;
        }

        $value = (int) $value;

        if ($value < $constraint->min() || $value > $constraint->max()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ min }}', (string) $constraint->min())
                ->setParameter('{{ max }}', (string) $constraint->max())
                ->addViolation();
        }
    }
}
