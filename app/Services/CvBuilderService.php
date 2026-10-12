<?php

namespace App\Services;

use App\Ai\CariKaryawanAgent;
use Illuminate\Support\Facades\Log;

class CvBuilderService
{
    /**
     * Polish and enhance CV sections using AI (Summary, Achievement bullets, or whole CV).
     */
    public function polishSection(string $section, array $data, string $language = 'id'): array
    {
        $systemInstruction = "Kamu adalah Konsultan Karir Senior & ATS Specialist (Applicant Tracking System). Tugasmu membantu menyusun dan memoles teks CV agar profesional, berbobot, menggunakan action verbs aktif terukur, dan lulus seleksi ATS.";

        $languageText = ($language === 'en') ? 'Bahasa Inggris yang profesional dan natural' : 'Bahasa Indonesia yang baku, formal, dan meyakinkan';
        $modelName = env('OPENAI_MODEL_CV', 'cv-generate');

        $prompt = match ($section) {
            'summary' => "Tuliskan 1 paragraf Ringkasan Profesional (Professional Summary) untuk CV (2-4 kalimat padat) dalam {$languageText} berdasarkan data berikut:
Nama: " . ($data['name'] ?? 'Pelamar') . "
Jabatan/Headline yang dituju: " . ($data['headline'] ?? '') . "
Keahlian Utama: " . ($data['skills'] ?? '') . "
Draft / Poin Awal: " . ($data['draft'] ?? '') . "
Instruksi: Tulis secara lugas, sebutkan keahlian kunci, pengalaman relevan, dan nilai tambah bagi perusahaan. Keluarkan HANYA teks ringkasan murni tanpa tanda kutip atau markdown.",

            'achievement' => "Ubah atau buatkan 2-4 poin pencapaian kerja (bullet points) untuk posisi: " . ($data['position'] ?? '') . " di " . ($data['company'] ?? '') . " dalam {$languageText}.
Draft / Catatan Tugas: " . ($data['draft'] ?? '') . "
Instruksi: 
1. Awali tiap baris dengan action verb aktif (misal: Mengembangkan, Mengoptimalkan, Memimpin, Meningkatkan).
2. Sertakan hasil terukur / metrik (persentase %, efisiensi waktu, jumlah output) jika relevan.
3. Satu poin per baris tanpa penomoran / bullet character (cukup baris baru).
4. Keluarkan HANYA teks murni.",

            default => "Optimalkan draf teks CV berikut dalam {$languageText}: " . ($data['draft'] ?? '')
        };

        try {
            $agent = new CariKaryawanAgent(instructions: $systemInstruction);
            $response = (string) $agent->prompt(
                prompt: $prompt,
                provider: 'openai-compatible',
                model: $modelName,
                timeout: 45
            );

            $cleanText = trim($response);
            $cleanText = preg_replace('/<think\b[^>]*>.*?<\/think>/is', '', $cleanText);
            $cleanText = preg_replace('/^.*?<\/think>\s*/is', '', $cleanText);
            $cleanText = preg_replace('/^```[a-zA-Z]*\s*/i', '', $cleanText);
            $cleanText = preg_replace('/\s*```$/', '', $cleanText);
            $cleanText = trim($cleanText);

            if (!empty($cleanText)) {
                return [
                    'success' => true,
                    'result' => $cleanText,
                    'model' => $modelName,
                ];
            }
        } catch (\Throwable $e) {
            Log::error('CvBuilderService AI error: ' . $e->getMessage());
        }

        return [
            'success' => false,
            'message' => 'Gagal memoles dengan AI saat ini. Silakan coba lagi.',
        ];
    }
}
