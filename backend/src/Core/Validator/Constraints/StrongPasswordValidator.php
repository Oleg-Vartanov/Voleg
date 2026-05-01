<?php

namespace App\Core\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class StrongPasswordValidator extends ConstraintValidator
{
    private const int MIN_LENGTH = 6;

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof StrongPassword) {
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

        if (strlen($value) < self::MIN_LENGTH) {
            $msg = 'Password must be at least ' . self::MIN_LENGTH . ' characters long.';
            $this->context->buildViolation($msg)->addViolation();
        }

        if (preg_match('/\s/', $value)) {
            $msg = 'The value can\'t contain spaces.';
            $this->context->buildViolation($msg)->addViolation();
        }

        if (!preg_match('/\d/', $value)) {
            $msg = 'Should have at least one digit.';
            $this->context->buildViolation($msg)->addViolation();
        }

        if (!preg_match('/[#?!@$%^&*-]/', $value)) {
            $msg = 'Should have at least one character from [#?!@$%^&*-].';
            $this->context->buildViolation($msg)->addViolation();
        }

        if (!preg_match('/[A-Z]/', $value)) {
            $msg = 'Should have at least one upper case character.';
            $this->context->buildViolation($msg)->addViolation();
        }
    }
}
