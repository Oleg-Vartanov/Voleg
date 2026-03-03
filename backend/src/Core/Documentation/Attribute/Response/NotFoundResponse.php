<?php

namespace App\Core\Documentation\Attribute\Response;

use OpenApi\Attributes\Response;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class NotFoundResponse extends Response
{
    public function __construct(string $description = 'Not Found')
    {
        parent::__construct(response: 404, description: $description);
    }
}
