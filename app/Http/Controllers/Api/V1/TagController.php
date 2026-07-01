<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index(): JsonResponse
    {
        $tags = Tag::all();
        return response()->json([
            'tags' => TagResource::collection($tags),
        ]);
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        Gate::authorize('create', Tag::class);

        $tag = Tag::create([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
        ]);

        return response()->json([
            'message' => 'Tag créé avec succès.',
            'tag' => new TagResource($tag),
        ], 201);
    }

    public function show(Tag $tag): JsonResponse
    {
        return response()->json([
            'tag' => new TagResource($tag),
        ]);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        Gate::authorize('delete', Tag::class);

        $tag->delete();

        return response()->json([
            'message' => 'Tag supprimé.',
        ]);
    }
}
