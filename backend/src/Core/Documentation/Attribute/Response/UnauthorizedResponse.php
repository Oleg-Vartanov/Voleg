<?php

namespace App\Core\Documentation\Attribute\Response;

use OpenApi\Attributes\Response;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class UnauthorizedResponse extends Response
{
    public function __construct()
    {
        parent::__construct(response: 401, description: 'Unauthorized');
    }
}
