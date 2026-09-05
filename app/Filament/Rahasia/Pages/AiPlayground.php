<?php

namespace App\Filament\Rahasia\Pages;

use App\Ai\Tools\DisekitarDatabaseTool;
use App\Models\AiChatSession;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Laravel\Ai\AnonymousAgent;
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

    public string $systemPrompt = 'Kamu adalah asisten AI yang cerdas, faktual, dan ramah untuk platform ulasan & rekomendasi lokal DiSekitar.';

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
                $this->systemPrompt = "Kamu adalah analis data cerdas untuk direktori DiSekitar. Gunakan tool database yang tersedia untuk memeriksa data riil (posts, cities, categories, landmarks, page_views) dan jawab dengan angka/informasi akurat.";
                $this->userPrompt = "Berapa jumlah post di kota Bandung?";
                $this->enableDatabaseAccess = true;
                break;
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
                $tools[] = new DisekitarDatabaseTool();
                $dbInstructions = "\n\nKamu memiliki akses langsung ke database live DiSekitar melalui tool database yang tersedia. Jika pertanyaan berkaitan dengan jumlah postingan, data kota, kategori, landmarks, page_views, rating, atau statistik lainnya, WAJIB panggil tool untuk mengecek data riil. Jawab langsung hasilnya dalam bahasa Indonesia yang ramah, informatif, dan akurat dalam format Markdown. JANGAN hanya menyajikan perintah query SQL mentah kecuali diminta secara eksplisit.";
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

            $agent = new AnonymousAgent(
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
