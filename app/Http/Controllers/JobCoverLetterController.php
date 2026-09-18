<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\JobCoverLetterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobCoverLetterController extends Controller
{
    /**
     * Handle AJAX generation of AI cover letter for a job.
     */
    public function generate(Request $request, JobCoverLetterService $service): JsonResponse
    {
        $validated = $request->validate([
            'post_id' => 'required|integer|exists:posts,id',
            'name' => 'required|string|max:120',
            'city' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:120',
            'phone' => 'nullable|string|max:40',
            'experience' => 'nullable|string|max:500',
            'skills' => 'nullable|string|max:500',
            'language' => 'nullable|string|in:id,en',
            'tone' => 'nullable|string|in:professional,enthusiastic,concise',
        ]);

        $post = Post::query()
            ->published()
            ->with(['category', 'city', 'province', 'details'])
            ->findOrFail($validated['post_id']);

        if (!$post->isJob()) {
            return response()->json([
                'success' => false,
                'message' => 'Surat lamaran hanya tersedia untuk postingan kategori Lowongan Kerja.',
            ], 422);
        }

        $result = $service->generate($post, $validated);

        \App\Models\ToolUsage::log(
            toolName: 'cover_letter',
            action: 'generate',
            postId: $post->id,
            targetPosition: $post->title,
            companyName: $post->company_name ?: ($post->job_company ?: null),
            metadata: [
                'language' => $validated['language'] ?? 'id',
                'tone' => $validated['tone'] ?? 'professional',
                'applicant_name' => $validated['name'],
                'model' => $result['model'] ?? 'cv-generate',
            ],
            status: !empty($result['cover_letter']) ? 'success' : 'failed'
        );

        return response()->json([
            'success' => true,
            'cover_letter' => $result['cover_letter'],
            'model' => $result['model'] ?? 'cv-generate',
        ]);
    }
}
