<?php

namespace App\Core\DTO\Documentation\Validator;

use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

/**
 * Used for documentation.
 */
#[OA\Schema(title: 'Validation Error Response')]
final class ValidationErrorResponse
{
    #[OA\Property(type: 'string', example: 'https://symfony.com/errors/validation')]
    public string $type;

    #[OA\Property(type: 'string', example: 'Validation Failed')]
    public string $title;

    #[OA\Property(type: 'string', example: 'password: This value is too short. It should have 6'
        . ' characters or more.\npassword: Should have at least one digit.')]
    public string $detail;

    /**
     * @var Violation[]
     */
    #[OA\Property(type: 'array', items: new OA\Items(ref: new Model(type: Violation::class)))]
    public array $violations;
}
