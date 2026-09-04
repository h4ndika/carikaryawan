<?php

namespace App\Filament\Rahasia\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Laravel\Ai\AnonymousAgent;
use UnitEnum;

class AiPlayground extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'AI Playground';

    protected static ?string $title = 'AI Playground & Tester';

    protected static ?string $slug = 'ai-playground';

    protected static string | UnitEnum | null $navigationGroup = 'Post';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.rahasia.pages.ai-playground';

    public string $provider = 'openai-compatible';

    public string $model = '';

    public string $systemPrompt = 'Kamu adalah asisten AI yang cerdas, faktual, dan ramah untuk platform ulasan & rekomendasi lokal DiSekitar.';

    public string $userPrompt = '';

    public array $messages = [];

    public ?string $errorMessage = null;

    public string $template = '';

    public function mount(): void
    {
        $this->provider = config('ai.default', 'openai-compatible');
        $this->model = (string) config('openai.model', 'Content-blog');
    }

    public function updatedProvider(string $val): void
    {
        if ($val === 'openai') {
            $this->model = (string) config('openai.model', 'Content-blog');
        } elseif ($val === 'gemini') {
            $this->model = 'gemini-2.5-flash';
        } elseif ($val === 'anthropic') {
            $this->model = 'claude-3-5-sonnet-latest';
        } elseif ($val === 'groq') {
            $this->model = 'llama-3.3-70b-versatile';
        } elseif ($val === 'deepseek') {
            $this->model = 'deepseek-chat';
        } else {
            $this->model = '';
        }
    }

    public function updatedTemplate(string $val): void
    {
        switch ($val) {
            case 'article':
                $this->systemPrompt = "Kamu adalah content writer profesional & pakar SEO untuk platform direktori wisata & kuliner DiSekitar.\nTulis artikel yang komprehensif, menarik, dan informatif dengan gaya bahasa casual semi-formal.";
                $this->userPrompt = "Tolong buatkan ulasan menarik dan rekomendasi lengkap untuk: [Nama Tempat, Kota]. Cantumkan keunggulan tempat, tips berkunjung, dan perkiraan suasana.";
                break;
            case 'seo':
                $this->systemPrompt = "Kamu adalah spesialis SEO on-page. Tugasmu adalah membuat Meta Title dan Meta Description yang memikat, natural, dan memiliki Click-Through-Rate (CTR) tinggi.";
                $this->userPrompt = "Buatkan 3 variasi Meta Title (maksimal 60 karakter) dan Meta Description (maksimal 155 karakter) untuk ulasan tempat berikut: [Nama Tempat, Kategori, Kota].";
                break;
            case 'summary':
                $this->systemPrompt = "Kamu adalah editor ringkasan konten. Buatkan ringkasan padat dan menarik dalam 2-3 paragraf.";
                $this->userPrompt = "Ringkas keistimewaan tempat berikut ini beserta alasan mengapa wajib dikunjungi: [Nama Tempat].";
                break;
            case 'general':
                $this->systemPrompt = 'Kamu adalah asisten AI yang cerdas, faktual, dan ramah untuk platform ulasan & rekomendasi lokal DiSekitar.';
                $this->userPrompt = '';
                break;
        }
    }

    public function sendMessage(): void
    {
        $promptText = trim($this->userPrompt);

        if (empty($promptText)) {
            Notification::make()
                ->title('Prompt tidak boleh kosong')
                ->warning()
                ->send();
            return;
        }

        $this->errorMessage = null;

        $this->messages[] = [
            'role' => 'user',
            'content' => $promptText,
            'timestamp' => now()->format('H:i:s'),
        ];

        $this->userPrompt = '';

        $startTime = microtime(true);

        try {
            $instructions = !empty($this->systemPrompt)
                ? $this->systemPrompt
                : 'You are a helpful AI assistant.';

            $agent = new AnonymousAgent(
                instructions: $instructions,
                messages: [],
                tools: []
            );

            $selectedProvider = $this->provider;
            $selectedModel = !empty($this->model) ? trim($this->model) : null;

            $response = $agent->prompt(
                prompt: $promptText,
                provider: $selectedProvider,
                model: $selectedModel,
                timeout: 120
            );

            $duration = round(microtime(true) - $startTime, 2);
            $outputText = (string) $response;

            $promptTokens = $response->usage->promptTokens ?? 0;
            $completionTokens = $response->usage->completionTokens ?? 0;
            $totalTokens = $response->usage->totalTokens ?? ($promptTokens + $completionTokens);

            $this->messages[] = [
                'role' => 'assistant',
                'content' => $outputText,
                'provider' => $selectedProvider,
                'model' => $selectedModel ?? ($response->meta->model ?? 'default'),
                'tokens' => [
                    'prompt' => $promptTokens,
                    'completion' => $completionTokens,
                    'total' => $totalTokens,
                ],
                'duration' => $duration . 's',
                'timestamp' => now()->format('H:i:s'),
            ];

            Notification::make()
                ->title('Respons AI Berhasil Dihasilkan')
                ->success()
                ->send();

        } catch (\Throwable $e) {
            $duration = round(microtime(true) - $startTime, 2);
            $this->errorMessage = $e->getMessage();

            $this->messages[] = [
                'role' => 'assistant',
                'content' => 'Error: ' . $e->getMessage(),
                'is_error' => true,
                'provider' => $this->provider,
                'model' => $this->model,
                'duration' => $duration . 's',
                'timestamp' => now()->format('H:i:s'),
            ];

            Notification::make()
                ->title('Gagal Menghubungi Model AI')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function clearMessages(): void
    {
        $this->messages = [];
        $this->errorMessage = null;

        Notification::make()
            ->title('Riwayat percakapan dibersihkan')
            ->info()
            ->send();
    }
}
