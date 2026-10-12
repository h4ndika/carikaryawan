<?php

namespace App\Filament\Rahasia\Pages;

use App\Ai\Tools\CariKaryawanDatabaseTool;
use App\Models\AiChatSession;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use App\Ai\CariKaryawanAgent;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Messages\MessageRole;
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

    public string $systemPrompt = 'Kamu adalah asisten AI yang cerdas, faktual, dan ramah untuk platform lowongan kerja & tools karier CariKaryawan.';

    public string $userPrompt = '';

    public bool $enableDatabaseAccess = true;

    public array $messages = [];

    public ?string $errorMessage = null;

    public string $template = '';

    public ?int $currentSessionId = null;

    public array $sessionsList = [];

    public bool $isGenerating = false;

    public ?string $pendingPrompt = null;

    public function mount(): void
    {
        $this->provider = config('ai.default', 'openai-compatible');
        $this->model = (string) config('openai.model', 'Content-blog');
        $this->loadSessionsList();
    }

    public function loadSessionsList(): void
    {
        $this->sessionsList = AiChatSession::query()
            ->orderByDesc('updated_at')
            ->take(30)
            ->get(['id', 'title', 'updated_at', 'model'])
            ->map(fn ($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'model' => $s->model,
                'updated_at' => $s->updated_at?->diffForHumans() ?? '',
            ])
            ->toArray();
    }

    public function newSession(): void
    {
        $this->currentSessionId = null;
        $this->messages = [];
        $this->errorMessage = null;
        $this->userPrompt = '';
        $this->isGenerating = false;
        $this->pendingPrompt = null;

        Notification::make()
            ->title('Sesi chat baru dimulai')
            ->info()
            ->send();
    }

    public function selectSession(int $id): void
    {
        $session = AiChatSession::find($id);

        if (! $session) {
            Notification::make()
                ->title('Sesi chat tidak ditemukan')
                ->danger()
                ->send();
            return;
        }

        $this->currentSessionId = $session->id;
        $this->provider = $session->provider ?: $this->provider;
        $this->model = $session->model ?: $this->model;
        $this->systemPrompt = $session->system_prompt ?? $this->systemPrompt;
        $this->enableDatabaseAccess = (bool) $session->enable_database_access;
        $this->messages = $session->messages ?? [];
        $this->errorMessage = null;
        $this->userPrompt = '';
        $this->isGenerating = false;
        $this->pendingPrompt = null;

        $this->dispatch('chat-scrolled');
    }

    public function deleteSession(int $id): void
    {
        AiChatSession::where('id', $id)->delete();

        if ($this->currentSessionId === $id) {
            $this->currentSessionId = null;
            $this->messages = [];
        }

        $this->loadSessionsList();

        Notification::make()
            ->title('Sesi chat dihapus')
            ->success()
            ->send();
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
            case 'analytics':
                $this->systemPrompt = "Kamu adalah analis data cerdas untuk CariKaryawan (portal lowongan kerja). Gunakan tool database yang tersedia untuk memeriksa data riil (posts lowongan kerja, cities, categories, page_views) dan jawab dengan angka/informasi akurat.";
                $this->userPrompt = "Berapa jumlah lowongan kerja di kota Bandung?";
                $this->enableDatabaseAccess = true;
                break;
            case 'job_description':
                $this->systemPrompt = "Kamu adalah content writer profesional & pakar SEO untuk portal lowongan kerja CariKaryawan.\nTulis deskripsi lowongan yang jelas, menarik, dan informatif dengan gaya bahasa profesional semi-formal.";
                $this->userPrompt = "Tolong buatkan deskripsi lowongan lengkap untuk: [Posisi, Perusahaan, Kota]. Cantumkan tanggung jawab, kualifikasi, benefit, dan tips melamar.";
                break;
            case 'seo':
                $this->systemPrompt = "Kamu adalah spesialis SEO on-page. Tugasmu adalah membuat Meta Title dan Meta Description yang memikat, natural, dan memiliki Click-Through-Rate (CTR) tinggi.";
                $this->userPrompt = "Buatkan 3 variasi Meta Title (maksimal 60 karakter) dan Meta Description (maksimal 155 karakter) untuk lowongan kerja berikut: [Posisi, Perusahaan, Kota].";
                break;
            case 'summary':
                $this->systemPrompt = "Kamu adalah editor ringkasan konten. Buatkan ringkasan padat dan menarik dalam 2-3 paragraf.";
                $this->userPrompt = "Ringkas lowongan berikut beserta alasan mengapa menarik dilamar: [Posisi, Perusahaan].";
                break;
            case 'general':
                $this->systemPrompt = 'Kamu adalah asisten AI yang cerdas, faktual, dan ramah untuk platform lowongan kerja & tools karier CariKaryawan.';
                $this->userPrompt = '';
                break;
        }
    }

    /**
     * Phase 1: User sends prompt. Instantly append to chat, clear textarea, and flag generating state.
     */
    public function submitPrompt(): void
    {
        $promptText = trim($this->userPrompt);

        if (empty($promptText)) {
            Notification::make()
                ->title('Prompt tidak boleh kosong')
                ->warning()
                ->send();
            return;
        }

        if ($this->isGenerating) {
            return;
        }

        $this->errorMessage = null;

        $this->messages[] = [
            'role' => 'user',
            'content' => $promptText,
            'timestamp' => now()->format('H:i:s'),
        ];

        $this->pendingPrompt = $promptText;
        $this->userPrompt = '';
        $this->isGenerating = true;

        $this->dispatch('chat-scrolled');
    }

    /**
     * Phase 2: Called right after UI render to process AI backend response and auto-update session.
     */
    public function processAiResponse(): void
    {
        if (! $this->isGenerating || empty($this->pendingPrompt)) {
            $this->isGenerating = false;
            return;
        }

        $promptText = $this->pendingPrompt;
        $startTime = microtime(true);

        try {
            $tools = [];
            $dbInstructions = '';

            if ($this->enableDatabaseAccess) {
                $tools[] = new CariKaryawanDatabaseTool();
                $dbInstructions = "\n\nKamu memiliki akses langsung ke database live CariKaryawan melalui tool database yang tersedia. Jika pertanyaan berkaitan dengan jumlah lowongan, data kota, kategori, page_views, atau statistik lainnya, WAJIB panggil tool untuk mengecek data riil (khusus page_views hari ini gunakan kolom viewed_date = CURDATE() atau DATE(created_at) = CURDATE(), bukan visited_at). Jawab langsung hasilnya dalam bahasa Indonesia yang ramah, informatif, dan akurat dalam format Markdown. JANGAN hanya menyajikan perintah query SQL mentah kecuali diminta secara eksplisit.";
            }

            $instructions = (!empty($this->systemPrompt) ? $this->systemPrompt : 'You are a helpful AI assistant.') . $dbInstructions;

            // Build conversation history (up to last 10 messages for conversational continuity)
            $history = [];
            $previousMessages = array_slice($this->messages, 0, -1);
            $recentMessages = array_slice($previousMessages, -10);

            foreach ($recentMessages as $msg) {
                if (!empty($msg['is_error'])) {
                    continue;
                }
                if (in_array($msg['role'] ?? '', ['user', 'assistant']) && !empty($msg['content'])) {
                    $role = $msg['role'] === 'user'
                        ? MessageRole::User
                        : MessageRole::Assistant;
                    $history[] = new Message($role, $msg['content']);
                }
            }

            $agent = new CariKaryawanAgent(
                instructions: $instructions,
                messages: $history,
                tools: $tools
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

            // Fallback: If outputText is empty but a step generated text, fallback to step text
            if (empty(trim($outputText))) {
                foreach (array_reverse($response->steps ?? []) as $step) {
                    if (!empty(trim($step->text ?? ''))) {
                        $outputText = $step->text;
                        break;
                    }
                }
            }

            $promptTokens = $response->usage->promptTokens ?? 0;
            $completionTokens = $response->usage->completionTokens ?? 0;
            $totalTokens = $response->usage->totalTokens ?? ($promptTokens + $completionTokens);

            // Extract tool calls if executed
            $toolCalls = [];
            foreach (($response->steps ?? []) as $step) {
                foreach (($step->toolCalls ?? []) as $tc) {
                    $toolCalls[] = [
                        'name' => $tc->name,
                        'arguments' => $tc->arguments,
                    ];
                }
            }

            $this->messages[] = [
                'role' => 'assistant',
                'content' => $outputText,
                'provider' => $selectedProvider,
                'model' => $selectedModel ?? ($response->meta->model ?? 'default'),
                'tool_calls' => $toolCalls,
                'tokens' => [
                    'prompt' => $promptTokens,
                    'completion' => $completionTokens,
                    'total' => $totalTokens,
                ],
                'duration' => $duration . 's',
                'timestamp' => now()->format('H:i:s'),
            ];

            // Save or update session in database
            $this->saveSession($promptText);

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
        } finally {
            $this->isGenerating = false;
            $this->pendingPrompt = null;
            $this->dispatch('chat-scrolled');
        }
    }

    protected function saveSession(string $firstPrompt): void
    {
        try {
            if ($this->currentSessionId) {
                $session = AiChatSession::find($this->currentSessionId);
                if ($session) {
                    $session->update([
                        'provider' => $this->provider,
                        'model' => $this->model,
                        'system_prompt' => $this->systemPrompt,
                        'enable_database_access' => $this->enableDatabaseAccess,
                        'messages' => $this->messages,
                    ]);
                }
            } else {
                $session = AiChatSession::create([
                    'user_id' => auth()->id(),
                    'title' => Str::limit($firstPrompt, 45, '...'),
                    'provider' => $this->provider,
                    'model' => $this->model,
                    'system_prompt' => $this->systemPrompt,
                    'enable_database_access' => $this->enableDatabaseAccess,
                    'messages' => $this->messages,
                ]);

                $this->currentSessionId = $session->id;
            }

            $this->loadSessionsList();
        } catch (\Throwable $e) {
            // Silently ignore session save error so chat isn't disrupted
        }
    }

    public function clearMessages(): void
    {
        $this->messages = [];
        $this->errorMessage = null;
        $this->isGenerating = false;
        $this->pendingPrompt = null;

        if ($this->currentSessionId) {
            AiChatSession::where('id', $this->currentSessionId)->update(['messages' => []]);
        }

        Notification::make()
            ->title('Riwayat percakapan dibersihkan')
            ->info()
            ->send();
    }
}
