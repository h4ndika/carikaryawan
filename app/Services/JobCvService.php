<?php

namespace App\Services;

use App\Ai\CariKaryawanAgent;
use App\Models\Post;
use Illuminate\Support\Facades\Log;

class JobCvService
{
    /**
     * Generate structured ATS-friendly CV data tailored for a job post using AI.
     */
    public function generate(Post $post, array $applicantData): array
    {
        $jobTitle = trim($post->title);
        $company = $post->company_name ?: ($post->job_company ?: 'Perusahaan Rekruter');

        $applicantName = trim($applicantData['name'] ?? 'Nama Lengkap');
        $applicantCity = trim($applicantData['city'] ?? '');
        if (empty($applicantCity)) {
            $cityName = $post->city?->name ?? 'Indonesia';
            $applicantCity = trim(preg_replace('/^(KOTA|KAB\.?|KABUPATEN)\s+/i', '', $cityName));
        }
        $applicantPhone = trim($applicantData['phone'] ?? '');
        $applicantEmail = trim($applicantData['email'] ?? '');
        $applicantLinkedin = trim($applicantData['linkedin'] ?? '');
        $applicantTargetTitle = trim($applicantData['target_title'] ?? $jobTitle);
        $applicantExperienceRaw = trim($applicantData['experience'] ?? '');
        $applicantEducationRaw = trim($applicantData['education'] ?? '');
        $applicantSkillsRaw = trim($applicantData['skills'] ?? '');
        $language = strtolower($applicantData['language'] ?? 'id'); // 'id' or 'en'

        // Ambil cuplikan kualifikasi dan isi lowongan kerja
        $jobRequirements = strip_tags($post->content);
        $jobRequirements = preg_replace('/\s+/', ' ', $jobRequirements);
        $jobRequirementsExcerpt = mb_substr($jobRequirements, 0, 1500);

        $systemInstruction = "Kamu adalah Konsultan Karir Senior & ATS Specialist (Applicant Tracking System). Tugasmu menyusun resume/CV profesional siap cetak yang lulus screening HRD dan sistem ATS.";

        $languageText = ($language === 'en') ? 'Bahasa Inggris (Professional English ATS)' : 'Bahasa Indonesia yang formal, baku, dan lugas';

        $prompt = "Susun data CV/Resume profesional yang dioptimalkan khusus untuk melamar posisi berikut:

LOWONGAN PEKERJAAN TARGET:
- Posisi: {$jobTitle}
- Perusahaan: {$company}
- Deskripsi & Kualifikasi:
{$jobRequirementsExcerpt}

PROFIL PELAMAR:
- Nama Lengkap: {$applicantName}
- Domisili: {$applicantCity}
- Kontak: Email: {$applicantEmail} | WA/Telp: {$applicantPhone}" . ($applicantLinkedin ? " | LinkedIn: {$applicantLinkedin}" : "") . "
- Target Posisi: {$applicantTargetTitle}
- Input Riwayat Pengalaman: " . ($applicantExperienceRaw ?: 'Fresh graduate / profesional berorientasi hasil.') . "
- Input Riwayat Pendidikan: " . ($applicantEducationRaw ?: 'Pendidikan terakhir relevan.') . "
- Input Keterampilan: " . ($applicantSkillsRaw ?: 'Komunikasi kerja, inisiatif, manajemen tugas.') . "

INSTRUKSI & FORMAT OUTPUT:
Gunakan bahasa: {$languageText}.
Kamu WAJIB mengembalikan output HANYA berupa JSON valid murni (tanpa tanda kutip markdown ```json atau pengantar apapun) dengan struktur berikut:
{
  \"summary\": \"Ringkasan profesional 3-4 kalimat padat yang memuat keyword ATS relevan lowongan di atas.\",
  \"skills\": [
    \"Hard skill / Soft skill 1\",
    \"Skill 2\",
    \"Skill 3\",
    \"Skill 4\",
    \"Skill 5\",
    \"Skill 6\"
  ],
  \"experiences\": [
    {
      \"role\": \"Nama Posisi/Pekerjaan\",
      \"company\": \"Nama Perusahaan / Organisasi / Proyek\",
      \"period\": \"Tahun / Periode (misal: 2022 - Sekarang)\",
      \"points\": [
        \"Poin pencapaian terukur diawali action verb yang relevan dengan lowongan target.\",
        \"Poin tanggung jawab / hasil kerja konkrit lainnya.\"
      ]
    }
  ],
  \"education\": [
    {
      \"degree\": \"Gelar / Jurusan (misal: S1 Ilmu Komunikasi / SMK Teknik)\",
      \"institution\": \"Nama Kampus / Sekolah\",
      \"period\": \"Tahun (misal: 2019 - 2023)\",
      \"description\": \"Keterangan singkat / IPK / Fokus studi bila relevan.\"
    }
  ]
}

PENTING:
1. Pastikan JSON valid. Jangan berikan teks di luar format JSON.
2. Gunakan action verbs kuat (misal: Mengelola, Mengembangkan, Mengoptimalkan, Memimpin).
3. Sesuaikan keyword pengalaman dan keahlian agar selaras dengan kualifikasi lowongan.";

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
            // Bersihkan markdown fence ```json / ```
            $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', $cleanJson);
            $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);
            $cleanJson = trim($cleanJson);

            // Ekstrak objek JSON pertama jika masih ada teks di luar JSON
            if (preg_match('/\{[\s\S]*\}/', $cleanJson, $matches)) {
                $cleanJson = $matches[0];
            }

            $parsed = json_decode($cleanJson, true);

            if (is_array($parsed) && !empty($parsed['summary'])) {
                return [
                    'success' => true,
                    'data' => [
                        'name' => $applicantName,
                        'city' => $applicantCity,
                        'phone' => $applicantPhone,
                        'email' => $applicantEmail,
                        'linkedin' => $applicantLinkedin,
                        'target_title' => $applicantTargetTitle,
                        'summary' => $parsed['summary'] ?? '',
                        'skills' => is_array($parsed['skills'] ?? null) ? $parsed['skills'] : [],
                        'experiences' => is_array($parsed['experiences'] ?? null) ? $parsed['experiences'] : [],
                        'education' => is_array($parsed['education'] ?? null) ? $parsed['education'] : [],
                    ],
                    'model' => $modelName,
                ];
            }
        } catch (\Throwable $e) {
            Log::error('JobCvService AI generation error: ' . $e->getMessage());
        }

        // Fallback jika AI gagal merespons atau JSON tidak valid
        return [
            'success' => true,
            'fallback' => true,
            'data' => [
                'name' => $applicantName,
                'city' => $applicantCity,
                'phone' => $applicantPhone,
                'email' => $applicantEmail,
                'linkedin' => $applicantLinkedin,
                'target_title' => $applicantTargetTitle,
                'summary' => "Profesional yang berdedikasi dan memiliki antusiasme tinggi untuk berkontribusi pada posisi {$jobTitle} di {$company}. Memiliki kemampuan adaptasi yang cepat, komunikasi efektif, dan rekam jejak kerja yang bertanggung jawab.",
                'skills' => array_values(array_filter(array_map('trim', explode(',', $applicantSkillsRaw ?: 'Komunikasi, Kerja Sama Tim, Manajemen Waktu, Problem Solving, Microsoft Office')))),
                'experiences' => [
                    [
                        'role' => $applicantTargetTitle,
                        'company' => 'Pengalaman Kerja / Portofolio',
                        'period' => '2023 - Sekarang',
                        'points' => [
                            $applicantExperienceRaw ?: "Menjalankan tugas dan tanggung jawab operasional dengan teliti serta mencapai target yang ditetapkan perusahaan.",
                            "Bekerja sama lintas tim secara efisien untuk mendukung produktivitas kerja harian."
                        ]
                    ]
                ],
                'education' => [
                    [
                        'degree' => $applicantEducationRaw ?: 'Pendidikan Terakhir',
                        'institution' => 'Institusi Pendidikan',
                        'period' => 'Lulus',
                        'description' => 'Fokus pada pengembangan keterampilan terapan dan integritas akademik.'
                    ]
                ]
            ],
            'model' => 'fallback',
        ];
    }
}
