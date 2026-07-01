<?php

namespace App\DTOs;

use App\Enums\PostStatus;

readonly class UpdatePostData
{
    public function __construct(
        public ?string $title = null,
        public ?string $content = null,
        public ?int $category_id = null,
        public ?PostStatus $status = null,
        public ?string $image_path = null,
        public ?array $tags = null
    ) {}

    public static function fromArray(array $data, ?string $image_path = null): self
    {
        return new self(
            title: $data['title'] ?? null,
            content: $data['content'] ?? null,
            category_id: isset($data['category_id']) ? (int) $data['category_id'] : null,
            status: isset($data['status']) ? PostStatus::from($data['status']) : null,
            image_path: $image_path,
            tags: $data['tags'] ?? null
        );
    }
}
