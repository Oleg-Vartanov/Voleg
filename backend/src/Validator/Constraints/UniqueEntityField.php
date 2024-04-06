<?php

namespace App\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

#[Attribute] class UniqueEntityField extends Constraint
{
    public string $message = 'This value is already used.';
    public string $entityClass;
    public string $field;

    public function __construct(string $entityClass, string $field)
    {
        parent::__construct(['entityClass' => $entityClass, 'field' => $field]);
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