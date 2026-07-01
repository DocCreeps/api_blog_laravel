<?php

namespace App\Repositories\Eloquent;

use App\Models\Comment;
use App\Repositories\Contracts\CommentRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentRepository implements CommentRepositoryInterface
{
    public function getForPost(int $postId, int $perPage = 20): LengthAwarePaginator
    {
        return Comment::query()
            ->with(['user', 'replies.user'])
            ->where('post_id', $postId)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Comment
    {
        return Comment::query()->with(['user', 'replies.user'])->find($id);
    }

    public function create(array $data): Comment
    {
        return Comment::create($data)->load(['user']);
    }

    public function update(Comment $comment, array $data): Comment
    {
        $comment->update($data);
        return $comment->load(['user']);
    }

    public function delete(Comment $comment): bool
    {
        return $comment->delete();
    }
}
