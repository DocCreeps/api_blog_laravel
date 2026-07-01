<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;

class FollowService
{
    public function toggleLikePost(User $user, Post $post): array
    {
        $liked = $user->likes()->toggle($post->id);
        
        $isLiked = count($liked['attached']) > 0;

        return [
            'liked' => $isLiked,
            'count' => $post->likes()->count(),
        ];
    }

    public function toggleFollowCategory(User $user, Category $category): array
    {
        $followed = $user->followedCategories()->toggle($category->id);
        
        $isFollowing = count($followed['attached']) > 0;

        return [
            'following' => $isFollowing,
        ];
    }

    public function toggleFollowUser(User $user, User $targetUser): array
    {
        if ($user->id === $targetUser->id) {
            throw new \InvalidArgumentException("Vous ne pouvez pas vous suivre vous-même.");
        }

        $followed = $user->following()->toggle($targetUser->id);
        
        $isFollowing = count($followed['attached']) > 0;

        return [
            'following' => $isFollowing,
        ];
    }
}
