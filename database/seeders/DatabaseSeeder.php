<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Enums\UserRole;
use App\Enums\PostStatus;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@blog.com',
            'role' => UserRole::ADMIN->value,
        ]);

        $writer1 = User::factory()->create([
            'name' => 'John Doe (Writer)',
            'email' => 'john@blog.com',
            'role' => UserRole::WRITER->value,
        ]);

        $writer2 = User::factory()->create([
            'name' => 'Jane Smith (Writer)',
            'email' => 'jane@blog.com',
            'role' => UserRole::WRITER->value,
        ]);

        $readers = User::factory(5)->create([
            'role' => UserRole::READER->value,
        ]);

        $categories = Category::factory(4)->create();
        $tags = Tag::factory(8)->create();

        $posts = collect();

        foreach (range(1, 10) as $i) {
            $post = Post::factory()->create([
                'user_id' => fake()->randomElement([$writer1->id, $writer2->id]),
                'category_id' => $categories->random()->id,
                'status' => PostStatus::PUBLISHED->value,
            ]);
            $post->tags()->sync($tags->random(rand(2, 4))->pluck('id'));
            $posts->push($post);
        }

        foreach (range(1, 3) as $i) {
            Post::factory()->create([
                'user_id' => $writer1->id,
                'category_id' => $categories->random()->id,
                'status' => PostStatus::DRAFT->value,
            ]);
        }

        foreach ($posts as $post) {
            $commentsCount = rand(2, 5);
            for ($c = 0; $c < $commentsCount; $c++) {
                $comment = Comment::factory()->create([
                    'post_id' => $post->id,
                    'user_id' => $readers->random()->id,
                    'parent_id' => null,
                ]);

                if (rand(0, 1)) {
                    Comment::factory()->create([
                        'post_id' => $post->id,
                        'user_id' => $readers->random()->id,
                        'parent_id' => $comment->id,
                    ]);
                }
            }

            $likingUsers = $readers->random(rand(1, 3));
            foreach ($likingUsers as $user) {
                $user->likes()->attach($post->id);
            }
        }

        foreach ($readers as $reader) {
            $reader->followedCategories()->attach($categories->random()->id);
            $reader->following()->attach($writer1->id);
        }
    }
}
