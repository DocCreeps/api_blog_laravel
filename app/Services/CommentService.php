<?php

namespace App\Services;

use App\DTOs\CreateCommentData;
use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Repositories\Contracts\CommentRepositoryInterface;

class CommentService
{
    public function __construct(
        protected CommentRepositoryInterface $commentRepository
    ) {}

    public function createComment(CreateCommentData $data, int $userId): Comment
    {
        return $this->commentRepository->create([
            'content' => $data->content,
            'post_id' => $data->post_id,
            'parent_id' => $data->parent_id,
            'user_id' => $userId,
            'status' => CommentStatus::APPROVED->value,
        ]);
    }

    public function approveComment(Comment $comment): Comment
    {
        return $this->commentRepository->update($comment, [
            'status' => CommentStatus::APPROVED->value,
        ]);
    }

    public function rejectComment(Comment $comment): Comment
    {
        return $this->commentRepository->update($comment, [
            'status' => CommentStatus::REJECTED->value,
        ]);
    }

    public function deleteComment(Comment $comment): bool
    {
        return $this->commentRepository->delete($comment);
    }
}
