<?php

namespace App\Repositories\Contracts;

use App\Models\Comment;
use Illuminate\Pagination\LengthAwarePaginator;

interface CommentRepositoryInterface
{
    public function getForPost(int $postId, int $perPage = 20): LengthAwarePaginator;
    public function findById(int $id): ?Comment;
    public function create(array $data): Comment;
    public function update(Comment $comment, array $data): Comment;
    public function delete(Comment $comment): bool;
}
