<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function view(?User $user, Post $post): bool
    {
        if ($post->status->value === 'published') {
            return true;
        }

        return $user && ($user->id === $post->user_id || $user->role === UserRole::ADMIN);
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::ADMIN || $user->role === UserRole::WRITER;
    }

    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->role === UserRole::ADMIN;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->role === UserRole::ADMIN;
    }

    public function viewAnyDrafts(User $user): bool
    {
        return $user->role === UserRole::ADMIN || $user->role === UserRole::WRITER;
    }
}
