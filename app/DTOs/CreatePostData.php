<?php

namespace App\DTOs;

use App\Enums\PostStatus;

readonly class CreatePostData
{
    public function __construct(
        public string $title,
        public string $content,
        public int $category_id,
        public PostStatus $status,
        public ?string $image_path = null,
        public array $tags = []
    ) {}

    public static function fromArray(array $data, ?string $image_path = null): self
    {
        return new self(
            title: $data['title'],
            content: $data['content'],
            category_id: (int) $data['category_id'],
            status: isset($data['status']) ? PostStatus::from($data['status']) : PostStatus::DRAFT,
            image_path: $image_path,
            tags: $data['tags'] ?? []
        );
    }
}
