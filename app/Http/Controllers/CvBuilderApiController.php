<?php

namespace App\Http\Controllers;

use App\Services\CvBuilderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class CvBuilderApiController extends Controller
{
    public function __construct(
        protected CvBuilderService $cvBuilderService
    ) {}

    /**
     * Polish CV section via AI
     */
    public function polish(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'section' => ['required', 'string', 'in:summary,achievement,general'],
            'data' => ['required', 'array'],
            'language' => ['nullable', 'string', 'in:id,en'],
        ]);

        $throttleKey = 'cv-polish:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 20)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak permintaan polish AI. Coba lagi dalam {$seconds} detik.",
            ], 429);
        }
        RateLimiter::hit($throttleKey, 60);

        $result = $this->cvBuilderService->polishSection(
            $validated['section'],
            $validated['data'],
            $validated['language'] ?? 'id'
        );

        return response()->json($result);
    }
}
