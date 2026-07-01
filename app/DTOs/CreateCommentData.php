<?php

namespace App\DTOs;

readonly class CreateCommentData
{
    public function __construct(
        public string $content,
        public int $post_id,
        public ?int $parent_id = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: $data['content'],
            post_id: (int) $data['post_id'],
            parent_id: isset($data['parent_id']) ? (int) $data['parent_id'] : null
        );
    }
}
