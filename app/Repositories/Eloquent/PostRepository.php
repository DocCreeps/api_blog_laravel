<?php

namespace App\Repositories\Eloquent;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class PostRepository implements PostRepositoryInterface
{
    public function getPublished(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Post::query()
            ->with(['author', 'category', 'tags'])
            ->where('status', PostStatus::PUBLISHED);

        return $this->applyFiltersAndPaginate($query, $filters, $perPage);
    }

    public function getAll(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Post::query()->with(['author', 'category', 'tags']);

        return $this->applyFiltersAndPaginate($query, $filters, $perPage);
    }

    public function findBySlug(string $slug): ?Post
    {
        return Post::query()
            ->with(['author', 'category', 'tags'])
            ->where('slug', $slug)
            ->first();
    }

    public function findById(int $id): ?Post
    {
        return Post::query()->with(['author', 'category', 'tags'])->find($id);
    }

    public function create(array $data): Post
    {
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $post = Post::create($data);

        if (!empty($tags)) {
            $post->tags()->sync($tags);
        }

        return $post->load(['author', 'category', 'tags']);
    }

    public function update(Post $post, array $data): Post
    {
        $tags = $data['tags'] ?? null;
        unset($data['tags']);

        $post->update(array_filter($data, fn($v) => $v !== null));

        if ($tags !== null) {
            $post->tags()->sync($tags);
        }

        return $post->load(['author', 'category', 'tags']);
    }

    public function delete(Post $post): bool
    {
        return $post->delete();
    }

    protected function applyFiltersAndPaginate($query, array $filters, int $perPage): LengthAwarePaginator
    {
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['tag_id'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('tags.id', $filters['tag_id']);
            });
        }

        if (!empty($filters['author_id'])) {
            $query->where('user_id', $filters['author_id']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('content', 'like', $search);
            });
        }

        $orderBy = $filters['order_by'] ?? 'created_at';
        $direction = $filters['direction'] ?? 'desc';
        $query->orderBy($orderBy, $direction);

        return $query->paginate($perPage);
    }
}
