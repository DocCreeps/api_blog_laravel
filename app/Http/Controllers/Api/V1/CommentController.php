<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\CreateCommentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Repositories\Contracts\CommentRepositoryInterface;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService,
        protected CommentRepositoryInterface $commentRepository
    ) {}

    public function index(int $postId): JsonResponse
    {
        $comments = $this->commentRepository->getForPost($postId, 20);

        return CommentResource::collection($comments)->response();
    }

    public function store(StoreCommentRequest $request): JsonResponse
    {
        Gate::authorize('create', Comment::class);

        $dto = CreateCommentData::fromArray($request->validated());
        $comment = $this->commentService->createComment($dto, $request->user()->id);

        return response()->json([
            'message' => 'Commentaire ajouté avec succès.',
            'comment' => new CommentResource($comment),
        ], 201);
    }

    public function update(Request $request, Comment $comment): JsonResponse
    {
        Gate::authorize('update', $comment);

        $request->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        $updatedComment = $this->commentRepository->update($comment, [
            'content' => $request->input('content'),
        ]);

        return response()->json([
            'message' => 'Commentaire mis à jour avec succès.',
            'comment' => new CommentResource($updatedComment),
        ]);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        Gate::authorize('delete', $comment);

        $this->commentService->deleteComment($comment);

        return response()->json([
            'message' => 'Commentaire supprimé avec succès.',
        ]);
    }

    public function approve(Comment $comment): JsonResponse
    {
        Gate::authorize('moderate', Comment::class);

        $approvedComment = $this->commentService->approveComment($comment);

        return response()->json([
            'message' => 'Commentaire approuvé.',
            'comment' => new CommentResource($approvedComment),
        ]);
    }

    public function reject(Comment $comment): JsonResponse
    {
        Gate::authorize('moderate', Comment::class);

        $rejectedComment = $this->commentService->rejectComment($comment);

        return response()->json([
            'message' => 'Commentaire rejeté.',
            'comment' => new CommentResource($rejectedComment),
        ]);
    }
}
