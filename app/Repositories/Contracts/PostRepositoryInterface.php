<?php

namespace App\Repositories\Contracts;

use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;

interface PostRepositoryInterface
{
    public function getPublished(int $perPage = 10, array $filters = []): LengthAwarePaginator;
    public function getAll(int $perPage = 10, array $filters = []): LengthAwarePaginator;
    public function findBySlug(string $slug): ?Post;
    public function findById(int $id): ?Post;
    public function create(array $data): Post;
    public function update(Post $post, array $data): Post;
    public function delete(Post $post): bool;
}
