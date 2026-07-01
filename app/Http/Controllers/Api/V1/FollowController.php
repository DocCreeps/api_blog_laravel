<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Services\FollowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __construct(
        protected FollowService $followService
    ) {}

    public function likePost(Request $request, Post $post): JsonResponse
    {
        $result = $this->followService->toggleLikePost($request->user(), $post);

        return response()->json([
            'message' => $result['liked'] ? 'Article aimé avec succès.' : 'Article désaimé avec succès.',
            'liked' => $result['liked'],
            'likes_count' => $result['count'],
        ]);
    }

    public function followCategory(Request $request, Category $category): JsonResponse
    {
        $result = $this->followService->toggleFollowCategory($request->user(), $category);

        return response()->json([
            'message' => $result['following'] ? 'Catégorie suivie avec succès.' : 'Vous ne suivez plus cette catégorie.',
            'following' => $result['following'],
        ]);
    }

    public function followUser(Request $request, User $user): JsonResponse
    {
        try {
            $result = $this->followService->toggleFollowUser($request->user(), $user);

            return response()->json([
                'message' => $result['following'] ? 'Auteur suivi avec succès.' : 'Vous ne suivez plus cet auteur.',
                'following' => $result['following'],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
