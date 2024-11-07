<?php

namespace App\DTO\User;

use OpenApi\Attributes as OA;

#[OA\Schema(title: 'User Update')]
class UpdateDto extends UserDto
{
}