<?php

declare(strict_types=1);

namespace App\User\Policies;

use App\Auth\Enums\Permission;
use App\User\Models\User;

final class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ViewUsers->value);
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->can(Permission::ViewUsers->value);
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->can(Permission::UpdateUsers->value);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->id !== $model->id && $user->can(Permission::DeleteUsers->value);
    }
}
