<?php

namespace App\DTO;

use App\Entity\User;
use App\Validator\Constraints as CustomAssert;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class UserDto
{
    #[Groups(['edit:admin'])]
    #[Assert\NotBlank, Assert\Type('string'), Assert\Email, Assert\Length(max: 180),
        CustomAssert\UniqueEntityField(entityClass: User::class, field: 'email')]
    public mixed $email;

    #[Groups(['edit:admin', 'edit:owner'])]
    #[Assert\NotBlank, Assert\Type('string'), Assert\Length(max: 255)]
    public mixed $displayName;
}