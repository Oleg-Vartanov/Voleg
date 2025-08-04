<?php

namespace App\Core\DTO\Documentation\Response;

use App\Core\DTO\Documentation\Validator\ValidationErrorResponseContent;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes\Response;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ValidationErrorResponse extends Response
{
    public function __construct()
    {
        parent::__construct(
            response: 422,
            description: 'Validation errors',
            content: new Model(type: ValidationErrorResponseContent::class)
        );
    }
}
