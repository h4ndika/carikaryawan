<?php

namespace App\Services;

use App\Ai\CariKaryawanAgent;
use App\Models\Post;
use Illuminate\Support\Facades\Log;

class InterviewPrepService
{
    /**
     * Generate categorized interview questions tailored for a specific job.
     */
    public function generateQuestions(array $params): array
    {
        $jobTitle = trim($params['job_title'] ?? 'Posisi Umum');
        $company = trim($params['company'] ?? 'Perusahaan');
        $jobDescription = trim($params['job_description'] ?? '');
        $level = $params['level'] ?? 'mid';
        $focus = $params['focus'] ?? 'all';
        $language = strtolower($params['language'] ?? 'id');

        $systemInstruction = "Kamu adalah Career Coach dan Interview Expert berpengalaman 15+ tahun melatih kandidat lolos wawancara kerja di perusahaan top Indonesia dan multinasional.";

        $languageText = ($language === 'en') ? 'Bahasa Inggris yang profesional' : 'Bahasa Indonesia yang natural dan formal';

        $levelText = match ($level) {
            'fresh' => 'Fresh Graduate / Entry Level (0-1 tahun pengalaman)',
            'senior' => 'Senior / Lead (5+ tahun pengalaman, expected leadership & strategic thinking)',
            default => 'Mid-Level / Profesional (2-5 tahun pengalaman)',
        };

        $focusInstruction = match ($focus) {
            'general' => "Fokuskan HANYA pada pertanyaan umum (perkenalan diri, motivasi, ekspektasi gaji, kelebihan-kekurangan).",
            'behavioral' => "Fokuskan HANYA pada pertanyaan behavioral berbasis metode STAR (Situation, Task, Action, Result).",
            'technical' => "Fokuskan HANYA pada pertanyaan teknis/fungsional yang spesifik sesuai posisi {$jobTitle}.",
            default => "Buatkan campuran dari semua kategori: general, behavioral, technical, dan company-specific.",
        };

        $jobDescExcerpt = '';
        if (!empty($jobDescription)) {
            $clean = strip_tags($jobDescription);
            $clean = preg_replace('/\s+/', ' ', $clean);
            $jobDescExcerpt = mb_substr($clean, 0, 1500);
        }

        $prompt = "Buatkan daftar pertanyaan wawancara kerja (interview questions) untuk posisi berikut:

DETAIL POSISI:
- Jabatan: {$jobTitle}
- Perusahaan: {$company}
- Level Kandidat: {$levelText}
" . ($jobDescExcerpt ? "- Deskripsi & Kualifikasi Posisi:\n{$jobDescExcerpt}\n" : "") . "
INSTRUKSI:
{$focusInstruction}
Bahasa output: {$languageText}.

Kamu WAJIB mengembalikan output HANYA berupa JSON valid murni (tanpa markdown fence atau teks apapun di luar JSON) dengan struktur berikut:
{
  \"general\": [
    {\"q\": \"Pertanyaan umum 1\", \"hint\": \"Tips singkat menjawab (1-2 kalimat)\"},
    {\"q\": \"Pertanyaan umum 2\", \"hint\": \"Tips singkat\"}
  ],
  \"behavioral\": [
    {\"q\": \"Ceritakan situasi...\", \"hint\": \"Gunakan metode STAR: ...\"}
  ],
  \"technical\": [
    {\"q\": \"Pertanyaan teknis spesifik posisi\", \"hint\": \"Fokus pada...\"}
  ],
  \"company_specific\": [
    {\"q\": \"Apa yang Anda ketahui tentang perusahaan?\", \"hint\": \"Riset dulu tentang...\"}
  ]
}

ATURAN:
1. Total 15-25 pertanyaan.
2. Setiap pertanyaan WAJIB punya field 'q' (pertanyaan) dan 'hint' (tips singkat 1-2 kalimat cara menjawab).
3. Sesuaikan tingkat kesulitan dan ekspektasi jawaban dengan level kandidat ({$levelText}).
4. Untuk kategori yang tidak diminta (jika focus bukan 'all'), isi array kosong [].
5. Pastikan JSON valid. HANYA keluarkan JSON.";

        $modelName = env('OPENAI_MODEL_CV', 'cv-generate');

        try {
            $agent = new CariKaryawanAgent(instructions: $systemInstruction);
            $response = (string) $agent->prompt(
                prompt: $prompt,
                provider: 'openai-compatible',
                model: $modelName,
                timeout: 50
            );

            $cleanJson = trim($response);
            // Bersihkan tag <think>...</think>
            $cleanJson = preg_replace('/<think\b[^>]*>.*?<\/think>/is', '', $cleanJson);
            $cleanJson = preg_replace('/^.*?<\/think>\s*/is', '', $cleanJson);
            // Bersihkan markdown fence
            $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', $cleanJson);
            $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);
            $cleanJson = trim($cleanJson);

            if (preg_match('/\{[\s\S]*\}/', $cleanJson, $matches)) {
                $cleanJson = $matches[0];
            }

            $parsed = json_decode($cleanJson, true);

            if (is_array($parsed)) {
                $categories = ['general', 'behavioral', 'technical', 'company_specific'];
                $data = [];
                $totalQ = 0;
                foreach ($categories as $cat) {
                    $items = is_array($parsed[$cat] ?? null) ? $parsed[$cat] : [];
                    $data[$cat] = $items;
                    $totalQ += count($items);
                }

                if ($totalQ > 0) {
                    return [
                        'success' => true,
                        'data' => $data,
                        'total' => $totalQ,
                        'model' => $modelName,
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::error('InterviewPrepService AI error: ' . $e->getMessage());
        }

        return [
            'success' => false,
            'message' => 'Gagal meng-generate pertanyaan interview. Silakan coba lagi.',
        ];
    }
}
