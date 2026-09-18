<?php

namespace App\Http\Controllers;

use App\Services\InterviewPrepService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class InterviewPrepController extends Controller
{
    public function __construct(
        protected InterviewPrepService $interviewService
    ) {}

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_title' => ['required', 'string', 'max:200'],
            'company' => ['nullable', 'string', 'max:200'],
            'job_description' => ['nullable', 'string', 'max:3000'],
            'level' => ['nullable', 'string', 'in:fresh,mid,senior'],
            'focus' => ['nullable', 'string', 'in:all,general,behavioral,technical'],
            'language' => ['nullable', 'string', 'in:id,en'],
        ]);

        $throttleKey = 'interview-prep:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 15)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak permintaan. Coba lagi dalam {$seconds} detik.",
            ], 429);
        }
        RateLimiter::hit($throttleKey, 60);

        $result = $this->interviewService->generateQuestions($validated);

        return response()->json($result);
    }
}
