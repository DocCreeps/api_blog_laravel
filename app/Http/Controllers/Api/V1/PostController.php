<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\CreatePostData;
use App\DTOs\UpdatePostData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function __construct(
        protected PostService $postService,
        protected PostRepositoryInterface $postRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['category_id', 'tag_id', 'author_id', 'search', 'order_by', 'direction']);
        $posts = $this->postRepository->getPublished(10, $filters);

        return PostResource::collection($posts)->response();
    }

    public function show(string $slug): JsonResponse
    {
        $post = $this->postRepository->findBySlug($slug);

        if (!$post) {
            return response()->json(['message' => 'Article non trouvé.'], 404);
        }

        if ($post->status->value !== 'published') {
            Gate::authorize('view', $post);
        }

        return response()->json([
            'post' => new PostResource($post),
        ]);
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        Gate::authorize('create', Post::class);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        $dto = CreatePostData::fromArray($request->validated(), $imagePath);
        $post = $this->postService->createPost($dto, $request->user()->id);

        return response()->json([
            'message' => 'Article créé avec succès.',
            'post' => new PostResource($post),
        ], 201);
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        Gate::authorize('update', $post);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        $dto = UpdatePostData::fromArray($request->validated(), $imagePath);
        $updatedPost = $this->postService->updatePost($post, $dto);

        return response()->json([
            'message' => 'Article mis à jour avec succès.',
            'post' => new PostResource($updatedPost),
        ]);
    }

    public function destroy(Post $post): JsonResponse
    {
        Gate::authorize('delete', $post);

        $this->postService->deletePost($post);

        return response()->json([
            'message' => 'Article supprimé avec succès.',
        ]);
    }

    public function drafts(Request $request): JsonResponse
    {
        Gate::authorize('viewAnyDrafts', Post::class);

        $filters = $request->only(['category_id', 'tag_id', 'search']);
        $filters['order_by'] = 'created_at';
        $filters['direction'] = 'desc';

        if ($request->user()->role->value === 'writer') {
            $filters['author_id'] = $request->user()->id;
        }

        $query = Post::query()->where('status', \App\Enums\PostStatus::DRAFT);
        
        if (!empty($filters['author_id'])) {
            $query->where('user_id', $filters['author_id']);
        }
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        $posts = $query->with(['author', 'category', 'tags'])->paginate(10);

        return PostResource::collection($posts)->response();
    }
}
