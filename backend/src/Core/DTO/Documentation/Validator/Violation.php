<?php

namespace App\Core\DTO\Documentation\Validator;

use OpenApi\Attributes as OA;

/**
 * Used for documentation.
 */
#[OA\Schema(title: 'Violation')]
final class Violation
{
    #[OA\Property(example: 'password')]
    public string $propertyPath;

    #[OA\Property(example: 'This value is too short. It should have 6 characters or more.')]
    public string $title;

    #[OA\Property(example: 'This value is too short. It should have {{ limit }} character or'
        . ' more.|This value is too short. It should have {{ limit }} characters or more.')]
    public string $template;

    #[OA\Property(example: ['{{ value }}' => "\"Str\"", '{{ limit }}' => '6', '{{ value_length }}' => '3'])]
    public array $parameters;

    #[OA\Property(example: 'urn:uuid:9ff3fdc4-b214-49db-8718-39c315e33d45')]
    public string $type;
}
