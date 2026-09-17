<?php

namespace App\Services;

use App\Ai\DisekitarAgent;
use App\Models\Post;
use Illuminate\Support\Facades\Log;

class JobCoverLetterService
{
    /**
     * Generate tailored cover letter for a specific job post using AI.
     */
    public function generate(Post $post, array $applicantData): array
    {
        $jobTitle = trim($post->title);
        $company = $post->company_name ?: ($post->job_company ?: 'Bapak/Ibu HRD / Tim Perekrut');
        
        // Kota pelamar / tanggal saat ini
        $applicantCity = trim($applicantData['city'] ?? '');
        if (empty($applicantCity)) {
            $cityName = $post->city?->name ?? 'Indonesia';
            $applicantCity = trim(preg_replace('/^(KOTA|KAB\.?|KABUPATEN)\s+/i', '', $cityName));
        }

        $applicantName = trim($applicantData['name'] ?? 'Pelamar');
        $applicantPhone = trim($applicantData['phone'] ?? '');
        $applicantEmail = trim($applicantData['email'] ?? '');
        $applicantSkills = trim($applicantData['skills'] ?? '');
        $applicantExperience = trim($applicantData['experience'] ?? '');
        $language = strtolower($applicantData['language'] ?? 'id'); // 'id' or 'en'
        $tone = strtolower($applicantData['tone'] ?? 'professional'); // 'professional', 'enthusiastic', 'concise'

        // Format tanggal hari ini (sekarang)
        $currentDateId = now()->translatedFormat('d F Y');
        $currentDateEn = now()->format('F d, Y');
        $currentDate = ($language === 'en') ? $currentDateEn : $currentDateId;

        // Ambil cuplikan kualifikasi dan isi lowongan kerja
        $jobRequirements = strip_tags($post->content);
        $jobRequirements = preg_replace('/\s+/', ' ', $jobRequirements);
        $jobRequirementsExcerpt = mb_substr($jobRequirements, 0, 1500);

        $systemInstruction = "Kamu adalah karir konsultan dan HR profesional spesialis pembuatan Cover Letter / Surat Lamaran Kerja terkurasi yang lolos filter ATS dan menarik perhatian perekrut di Indonesia.";

        $languageText = ($language === 'en') ? 'Bahasa Inggris yang natural dan profesional' : 'Bahasa Indonesia yang baik, sopan, dan formal';

        $toneGuideline = match ($tone) {
            'enthusiastic' => 'Penuh antusiasme, bersemangat, proaktif, dan menunjukkan passion yang tinggi.',
            'concise' => 'Singkat, padat, to-the-point, fokus pada hasil pencapaian konkrit.',
            default => 'Profesional, berwibawa, santun, dan meyakinkan.',
        };

        $prompt = "Buatkan Surat Lamaran Kerja (Cover Letter) resmi berdasarkan data berikut:

DATA LOWONGAN:
- Posisi: {$jobTitle}
- Perusahaan: {$company}
- Deskripsi & Kualifikasi Loker:
{$jobRequirementsExcerpt}

DATA PELAMAR & TANGGAL SURAT:
- Kota Asal / Domisili Surat: {$applicantCity}
- Tanggal Surat: {$currentDate}
- Nama Lengkap: {$applicantName}
- Kontak: " . ($applicantEmail ? "Email: {$applicantEmail}" : "") . " " . ($applicantPhone ? "Telp/WA: {$applicantPhone}" : "") . "
- Riwayat / Latar Belakang: " . ($applicantExperience ?: 'Fresh graduate / profesional berdedikasi') . "
- Keterampilan / Skill Unggulan: " . ($applicantSkills ?: 'Komunikasi yang baik, adaptif, teliti, dan disiplin') . "

ATURAN PENULISAN:
1. Bahasa: Gunakan {$languageText}.
2. Tone: {$toneGuideline}.
3. Baris Pembuka / Header:
   - WAJIB gunakan baris tempat dan tanggal berikut secara persis di bagian paling atas: '{$applicantCity}, {$currentDate}'
   - Diikuti tujuan surat: Kepada Yth. Tim HRD / Perekrut {$company}
4. Struktur Isi Surat:
   - Salam pembuka formal
   - Paragraf 1: Pengenalan diri, posisi yang dilamar ({$jobTitle}), dan motivasi melamar
   - Paragraf 2: Relevansi pengalaman/keterampilan pelamar dengan kebutuhan posisi di atas (hubungkan secara logis)
   - Paragraf 3: Nilai tambah, kontribusi nyata yang siap diberikan, dan harapan untuk sesi interview
   - Penutup formal & tanda tangan: {$applicantName}
5. Jangan gunakan tanda kurung placeholder seperti [Nama Perusahaan] atau [Keterampilan]. Gunakan data yang diberikan secara langsung.
6. Keluarkan HANYA teks surat lamaran murni (plain text yang rapi dengan paragraf berjarak), tanpa bungkus markdown ``` atau catatan pengantar.";

        $modelName = env('OPENAI_MODEL_CV', 'cv-generate');

        try {
            $agent = new DisekitarAgent(instructions: $systemInstruction);
            $response = (string) $agent->prompt(
                prompt: $prompt,
                provider: 'openai-compatible',
                model: $modelName,
                timeout: 45
            );

            $cleanText = trim($response);
            $cleanText = preg_replace('/^```[a-zA-Z]*\s*/i', '', $cleanText);
            $cleanText = preg_replace('/\s*```$/', '', $cleanText);
            $cleanText = trim($cleanText);

            if (!empty($cleanText)) {
                return [
                    'success' => true,
                    'cover_letter' => $cleanText,
                    'model' => $modelName,
                ];
            }
        } catch (\Throwable $e) {
            Log::error('JobCoverLetterService generation error: ' . $e->getMessage());
        }

        // Fallback generator jika AI sedang timeout atau mengalami limitasi
        return [
            'success' => true,
            'cover_letter' => $this->buildFallbackCoverLetter($jobTitle, $company, $applicantCity, $applicantName, $applicantEmail, $applicantPhone, $applicantExperience, $applicantSkills, $language),
            'model' => 'fallback',
        ];
    }

    /**
     * Fallback clean template if AI call fails
     */
    protected function buildFallbackCoverLetter(
        string $jobTitle,
        string $company,
        string $city,
        string $name,
        string $email,
        string $phone,
        string $exp,
        string $skills,
        string $lang
    ): string {
        $dateStr = now()->translatedFormat('d F Y');

        if ($lang === 'en') {
            return "{$city}, {$dateStr}\n\n" .
                "To: Hiring Manager\n" .
                "{$company}\n" .
                "{$city}\n\n" .
                "Dear Hiring Manager,\n\n" .
                "I am writing to express my strong interest in the {$jobTitle} position at {$company}. With my background in " . ($exp ?: 'this field') . " and skills in " . ($skills ?: 'relevant tasks') . ", I am confident in my ability to make a meaningful contribution to your team.\n\n" .
                "Throughout my career and educational journey, I have developed a solid foundation in problem solving, effective communication, and adaptability. I am particularly excited about {$company} because of your commitment to excellence and growth in {$city}.\n\n" .
                "I would welcome the opportunity to discuss how my qualifications align with your needs in an interview. Thank you for your time and consideration.\n\n" .
                "Sincerely,\n\n" .
                "{$name}\n" .
                ($phone ? "Phone: {$phone}\n" : "") .
                ($email ? "Email: {$email}" : "");
        }

        return "{$city}, {$dateStr}\n\n" .
            "Kepada Yth.\n" .
            "Tim HRD / Perekrut {$company}\n" .
            "di {$city}\n\n" .
            "Dengan hormat,\n\n" .
            "Sehubungan dengan informasi lowongan pekerjaan untuk posisi {$jobTitle} di {$company} yang saya peroleh, melalui surat ini saya bermaksud untuk mengajukan diri guna bergabung dengan tim yang Bapak/Ibu pimpin.\n\n" .
            "Saya memiliki latar belakang di bidang " . ($exp ?: 'terkait') . " serta didukung oleh kemampuan dalam " . ($skills ?: 'bidang teknis dan komunikasi yang adaptif') . ". Saya adalah pribadi yang disiplin, memiliki etos kerja tinggi, cepat belajar hal baru, dan terbiasa bekerja secara mandiri maupun berkolaborasi dalam tim.\n\n" .
            "Besar harapan saya untuk diberikan kesempatan menghadiri sesi wawancara guna menjelaskan lebih mendalam mengenai potensi dan kontribusi nyata yang dapat saya berikan untuk kemajuan {$company}.\n\n" .
            "Demikian surat lamaran ini saya sampaikan. Atas perhatian dan kesempatan yang Bapak/Ibu berikan, saya ucapkan terima kasih.\n\n" .
            "Hormat saya,\n\n" .
            "{$name}\n" .
            ($phone ? "Telp/WA: {$phone}\n" : "") .
            ($email ? "Email: {$email}" : "");
    }
}
