<?php

namespace App\DTO\User\Request;

use OpenApi\Attributes as OA;

#[OA\Schema(title: 'User Update')]
class UpdateDto extends UserDto
{
}