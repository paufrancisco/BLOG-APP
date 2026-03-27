<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(): JsonResponse
    {
        $posts = Post::orderBy('created_at', 'desc')->get();
        return response()->json($posts);
    }

    public function show(Post $post): JsonResponse
    {
        return response()->json($post);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'author'   => ['required', 'string', 'max:100'],
            'body'     => ['required', 'string'],
            'status'   => ['required', 'in:draft,published'],
        ]);

        $post = Post::create($request->only('title', 'category', 'author', 'body', 'status'));

        return response()->json($post, 201);
    }

    public function update(Request $request, Post $post): JsonResponse
    {
        $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'author'   => ['required', 'string', 'max:100'],
            'body'     => ['required', 'string'],
            'status'   => ['required', 'in:draft,published'],
        ]);

        $post->update($request->only('title', 'category', 'author', 'body', 'status'));

        return response()->json($post);
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->delete();
        return response()->json(null, 204);
    }
}