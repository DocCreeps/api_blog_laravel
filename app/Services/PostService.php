<?php

namespace App\Services;

use App\DTOs\CreatePostData;
use App\DTOs\UpdatePostData;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PostService
{
    public function __construct(
        protected PostRepositoryInterface $postRepository
    ) {}

    public function createPost(CreatePostData $data, int $authorId): Post
    {
        $slug = $this->generateUniqueSlug($data->title);

        return $this->postRepository->create([
            'title' => $data->title,
            'slug' => $slug,
            'content' => $data->content,
            'image_path' => $data->image_path,
            'status' => $data->status->value,
            'user_id' => $authorId,
            'category_id' => $data->category_id,
            'tags' => $data->tags,
        ]);
    }

    public function updatePost(Post $post, UpdatePostData $data): Post
    {
        $updateData = [];

        if ($data->title !== null) {
            $updateData['title'] = $data->title;
            $updateData['slug'] = $this->generateUniqueSlug($data->title, $post->id);
        }

        if ($data->content !== null) {
            $updateData['content'] = $data->content;
        }

        if ($data->category_id !== null) {
            $updateData['category_id'] = $data->category_id;
        }

        if ($data->status !== null) {
            $updateData['status'] = $data->status->value;
        }

        if ($data->image_path !== null) {
            if ($post->image_path) {
                Storage::disk('public')->delete($post->image_path);
            }
            $updateData['image_path'] = $data->image_path;
        }

        if ($data->tags !== null) {
            $updateData['tags'] = $data->tags;
        }

        return $this->postRepository->update($post, $updateData);
    }

    public function deletePost(Post $post): bool
    {
        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }

        return $this->postRepository->delete($post);
    }

    protected function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (Post::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
