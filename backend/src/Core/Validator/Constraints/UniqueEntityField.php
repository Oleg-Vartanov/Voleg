<?php

namespace App\Core\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute] class UniqueEntityField extends Constraint
{
    public string $message = 'This value is already used.';
    public string $entityClass;
    public string $field;

    /**
     * @param mixed[] $options
     */
    public function __construct(
        string $entityClass,
        string $field,
        array $options = [],
        ?array $groups = null,
        mixed $payload = null
    ) {
        $options = array_merge(['entityClass' => $entityClass, 'field' => $field], $options);

        parent::__construct($options, $groups, $payload);
    }

    /** @inheritDoc */
    public function getRequiredOptions(): array
    {
        return ['entityClass', 'field'];
    }

    /** @inheritDoc */
    public function getTargets(): array|string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
