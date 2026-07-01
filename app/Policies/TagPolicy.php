<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class TagPolicy
{
    public function create(User $user): bool
    {
        return $user->role === UserRole::ADMIN || $user->role === UserRole::WRITER;
    }

    public function delete(User $user): bool
    {
        return $user->role === UserRole::ADMIN;
    }
}
