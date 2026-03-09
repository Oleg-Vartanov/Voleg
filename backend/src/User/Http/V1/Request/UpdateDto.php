<?php

namespace App\User\Http\V1\Request;

use OpenApi\Attributes as OA;

#[OA\Schema(title: 'User Update')]
class UpdateDto extends UserDto
{
}
