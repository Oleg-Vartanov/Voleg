<?php

namespace App\User\Http\V1\Trait;

use App\Core\Enum\Group;
use App\User\Entity\User;
use App\User\Enum\RoleEnum;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @method User|null getUser()
 */
trait UserControllerTrait
{
    // todo: rename || refactor || remove ?
    protected function checkModifyAccess(int $id): void
    {
        if (!$this->isAdmin() && !$this->isOwner($id)) {
            throw new AccessDeniedHttpException();
        }
    }

    protected function isAdmin(): bool
    {
        return $this->isGranted(RoleEnum::ROLE_ADMIN->value);
    }

    protected function isCurrentUser(int $id): bool
    {
        return $this->getUser()?->getId() === $id;
    }

    protected function isOwner(int $id): bool
    {
        return $this->isCurrentUser($id);
    }

    /**
     * @return string[]
     */
    protected function showGroups(?int $userIdToShow = null): array
    {
        // TODO: refactor ?
        $groups = [Group::public->value];
        if ($this->isGranted(RoleEnum::ROLE_ADMIN->value)) {
            $groups[] = Group::admin->value;
        }
        if (!is_null($userIdToShow) && $this->isOwner($userIdToShow)) {
            $groups[] = Group::owner->value;
        }

        return $groups;
    }
}
