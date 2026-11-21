<?php

namespace App\Core\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordValidator extends ConstraintValidator
{
    private const int MIN_LENGTH = 8;
    private const int MAX_LENGTH = 255;

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Password) {
            return;
        }

        if ($value === null || $value === '') {
            return; // NotBlank should handle this
        }

        if (!is_string($value)) {
            $msg = 'The value must be a string.';
            $this->context->buildViolation($msg)->addViolation();

            return;
        }

        $length = mb_strlen($value);

        if ($length < self::MIN_LENGTH) {
            $msg = 'Password must be at least ' . self::MIN_LENGTH . ' characters long.';
            $this->context->buildViolation($msg)->addViolation();
            return;
        }

        if ($length > self::MAX_LENGTH) {
            $msg = 'Password must be at most ' . self::MAX_LENGTH . ' characters long.';
            $this->context->buildViolation($msg)->addViolation();
            return;
        }

        $this->context->getValidator()
            ->inContext($this->context)
            ->validate($value, new NotCompromisedPassword(skipOnError: true));
    }
}
