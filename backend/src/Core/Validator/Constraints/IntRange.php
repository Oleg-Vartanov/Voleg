<?php

namespace App\Core\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class IntRange extends Constraint
{
    /** Signed 32-bit floor. Written as an expression so the literal stays within a PHP int. */
    public const int SIGNED_MIN = -2147483647 - 1;

    public const int SIGNED_MAX = 2147483647;

    public const int UNSIGNED_MIN = 0;

    public const int UNSIGNED_MAX = 4294967295;

    public string $message = 'This value should be between {{ min }} and {{ max }}.';

    /**
     * @param bool $signed false for unsigned
     * @param mixed[] $options
     */
    public function __construct(
        public bool $signed = true,
        array $options = [],
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);
    }

    public function min(): int
    {
        return $this->signed ? self::SIGNED_MIN : self::UNSIGNED_MIN;
    }

    public function max(): int
    {
        return $this->signed ? self::SIGNED_MAX : self::UNSIGNED_MAX;
    }
}
