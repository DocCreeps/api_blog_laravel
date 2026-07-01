<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Post;
use App\Models\Comment;
use App\Enums\UserRole;
use App\Enums\PostStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('un visiteur anonyme peut voir les articles publies', function () {
    $category = Category::factory()->create();
    $author = User::factory()->create(['role' => UserRole::WRITER]);
    
    $post = Post::factory()->create([
        'category_id' => $category->id,
        'user_id' => $author->id,
        'status' => PostStatus::PUBLISHED,
    ]);

    $response = $this->getJson('/api/v1/posts');

    $response->assertStatus(200)
             ->assertJsonFragment(['title' => $post->title]);
});

test('un visiteur anonyme ne peut pas voir les brouillons', function () {
    $category = Category::factory()->create();
    $author = User::factory()->create(['role' => UserRole::WRITER]);
    
    $post = Post::factory()->create([
        'category_id' => $category->id,
        'user_id' => $author->id,
        'status' => PostStatus::DRAFT,
    ]);

    $response = $this->getJson("/api/v1/posts/slug/{$post->slug}");

    $response->assertStatus(403);
});

test('un lecteur ne peut pas modifier ou supprimer un article', function () {
    $reader = User::factory()->create(['role' => UserRole::READER]);
    $category = Category::factory()->create();
    $author = User::factory()->create(['role' => UserRole::WRITER]);
    
    $post = Post::factory()->create([
        'category_id' => $category->id,
        'user_id' => $author->id,
        'status' => PostStatus::PUBLISHED,
    ]);

    // Modifier
    $responseUpdate = $this->actingAs($reader)
                           ->putJson("/api/v1/posts/{$post->id}", [
                               'title' => 'Titre modifie',
                           ]);
    $responseUpdate->assertStatus(403);

    // Supprimer
    $responseDelete = $this->actingAs($reader)
                           ->deleteJson("/api/v1/posts/{$post->id}");
    $responseDelete->assertStatus(403);
});

test('un auteur peut modifier et supprimer son propre article', function () {
    $author = User::factory()->create(['role' => UserRole::WRITER]);
    $category = Category::factory()->create();
    
    $post = Post::factory()->create([
        'category_id' => $category->id,
        'user_id' => $author->id,
        'status' => PostStatus::PUBLISHED,
    ]);

    // Modifier
    $responseUpdate = $this->actingAs($author)
                           ->putJson("/api/v1/posts/{$post->id}", [
                               'title' => 'Nouveau Titre',
                               'content' => 'Contenu modifie',
                               'category_id' => $category->id,
                           ]);

    $responseUpdate->assertStatus(200)
                   ->assertJsonPath('post.title', 'Nouveau Titre');

    // Supprimer
    $responseDelete = $this->actingAs($author)
                           ->deleteJson("/api/v1/posts/{$post->id}");
    $responseDelete->assertStatus(200);
});

test('un utilisateur peut commenter et repondre a un commentaire', function () {
    $user = User::factory()->create(['role' => UserRole::READER]);
    $post = Post::factory()->create();

    // Commentaire racine
    $responseComment = $this->actingAs($user)
                            ->postJson('/api/v1/comments', [
                                'content' => 'Mon commentaire de test',
                                'post_id' => $post->id,
                            ]);

    $responseComment->assertStatus(201);
    $commentId = $responseComment->json('comment.id');

    // Réponse au commentaire
    $responseReply = $this->actingAs($user)
                          ->postJson('/api/v1/comments', [
                              'content' => 'Ma reponse de test',
                              'post_id' => $post->id,
                              'parent_id' => $commentId,
                          ]);

    $responseReply->assertStatus(201)
                  ->assertJsonPath('comment.parent_id', $commentId);
});

test('un utilisateur peut liker un article et suivre une categorie ou un auteur', function () {
    $user = User::factory()->create(['role' => UserRole::READER]);
    $author = User::factory()->create(['role' => UserRole::WRITER]);
    $category = Category::factory()->create();
    $post = Post::factory()->create(['user_id' => $author->id, 'category_id' => $category->id]);

    // Like post
    $responseLike = $this->actingAs($user)
                         ->postJson("/api/v1/posts/{$post->id}/like");
    $responseLike->assertStatus(200)
                 ->assertJsonPath('liked', true);

    // Follow category
    $responseFollowCat = $this->actingAs($user)
                               ->postJson("/api/v1/categories/{$category->id}/follow");
    $responseFollowCat->assertStatus(200)
                      ->assertJsonPath('following', true);

    // Follow author
    $responseFollowUser = $this->actingAs($user)
                                ->postJson("/api/v1/users/{$author->id}/follow");
    $responseFollowUser->assertStatus(200)
                       ->assertJsonPath('following', true);
});

test('protection des donnees sensibles des profils', function () {
    $user1 = User::factory()->create(['role' => UserRole::READER, 'email' => 'user1@example.com']);
    $user2 = User::factory()->create(['role' => UserRole::READER, 'email' => 'user2@example.com']);

    // Un utilisateur ne peut pas voir l'email d'un autre utilisateur
    $response = $this->actingAs($user1)
                     ->getJson("/api/v1/auth/me"); // Voir soi-même, ok
    $response->assertStatus(200)
             ->assertJsonPath('user.email', 'user1@example.com');

    // Dans les posts publiques, les détails de l'auteur sont anonymisés (pas d'email)
    $category = Category::factory()->create();
    $post = Post::factory()->create([
        'user_id' => $user2->id,
        'category_id' => $category->id,
        'status' => PostStatus::PUBLISHED,
    ]);

    $responsePost = $this->getJson('/api/v1/posts');
    $responsePost->assertStatus(200);
    
    // L'email du user2 ne doit pas figurer dans le JSON
    $this->assertStringNotContainsString('user2@example.com', $responsePost->getContent());
});
