<?php

namespace App\User\DTO\Request;

use OpenApi\Attributes as OA;

#[OA\Schema(title: 'User Update')]
class UpdateDto extends UserDto
{
}
