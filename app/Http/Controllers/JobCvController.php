<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\JobCvService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class JobCvController extends Controller
{
    public function __construct(
        protected JobCvService $cvService
    ) {}

    /**
     * Handle AJAX generation of ATS-friendly CV.
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'post_id' => ['required', 'integer', 'exists:posts,id'],
            'name' => ['required', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'target_title' => ['nullable', 'string', 'max:150'],
            'experience' => ['nullable', 'string', 'max:2000'],
            'education' => ['nullable', 'string', 'max:1000'],
            'skills' => ['nullable', 'string', 'max:1000'],
            'language' => ['nullable', 'string', 'in:id,en'],
        ]);

        $post = Post::findOrFail($validated['post_id']);

        if (!$post->isJob()) {
            return response()->json([
                'success' => false,
                'message' => 'Postingan ini bukan lowongan kerja.',
            ], 422);
        }

        // IP Rate Limiting: max 15 kali generate CV per menit
        $throttleKey = 'job-cv:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 15)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak permintaan generate CV. Coba lagi dalam {$seconds} detik.",
            ], 429);
        }
        RateLimiter::hit($throttleKey, 60);

        $result = $this->cvService->generate($post, $validated);

        return response()->json($result);
    }
}
