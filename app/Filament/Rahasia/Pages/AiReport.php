<?php

namespace App\Filament\Rahasia\Pages;

use App\Ai\CariKaryawanAgent;
use App\Ai\Tools\CariKaryawanDatabaseTool;
use App\Models\AiReport as AiReportModel;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use UnitEnum;

class AiReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static ?string $navigationLabel = 'AI Report';

    protected static ?string $title = 'AI Report & Executive Intelligence';

    protected static ?string $slug = 'ai-report';

    protected static string | UnitEnum | null $navigationGroup = 'Post';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.rahasia.pages.ai-report';

    public string $provider = 'openai-compatible';

    public string $model = 'Content-blog';

    public string $userPrompt = '';

    public bool $enableDatabaseAccess = true;

    public bool $isGenerating = false;

    public ?string $pendingPrompt = null;

    public ?int $currentReportId = null;

    public ?array $report = null;

    public ?string $rawText = null;

    public array $toolCalls = [];

    public ?float $duration = null;

    public array $tokens = [];

    public array $reportsList = [];

    public ?string $errorMessage = null;

    public string $activeTab = 'dashboard'; // 'dashboard', 'raw', 'tools'

    public function mount(): void
    {
        $this->provider = config('ai.default', 'openai-compatible');
        $this->model = (string) config('openai.model', 'Content-blog');
        $this->loadReportsList();

        // Auto-load latest report if available
        if (! empty($this->reportsList)) {
            $this->selectReport($this->reportsList[0]['id'], showNotification: false);
        }
    }

    public function loadReportsList(): void
    {
        $this->reportsList = AiReportModel::query()
            ->orderByDesc('created_at')
            ->take(25)
            ->get(['id', 'title', 'created_at', 'model'])
            ->map(fn ($r) => [
                'id' => $r->id,
                'title' => $r->title,
                'model' => $r->model,
                'created_at' => $r->created_at?->diffForHumans() ?? '',
            ])
            ->toArray();
    }

    public function applyTemplate(string $text): void
    {
        $this->userPrompt = $text;
    }

    public function newReport(): void
    {
        $this->currentReportId = null;
        $this->report = null;
        $this->rawText = null;
        $this->toolCalls = [];
        $this->duration = null;
        $this->tokens = [];
        $this->errorMessage = null;
        $this->userPrompt = '';
        $this->isGenerating = false;
        $this->pendingPrompt = null;
        $this->activeTab = 'dashboard';

        Notification::make()
            ->title('Siap membuat laporan baru')
            ->info()
            ->send();
    }

    public function selectReport(int $id, bool $showNotification = true): void
    {
        $aiReport = AiReportModel::find($id);

        if (! $aiReport) {
            if ($showNotification) {
                Notification::make()
                    ->title('Laporan tidak ditemukan')
                    ->danger()
                    ->send();
            }
            return;
        }

        $this->currentReportId = $aiReport->id;
        $this->report = $aiReport->report_data;
        $this->rawText = $aiReport->raw_text;
        $this->toolCalls = $aiReport->tool_calls ?? [];
        $this->duration = $aiReport->duration;
        $this->tokens = $aiReport->tokens ?? [];
        $this->provider = $aiReport->provider ?: $this->provider;
        $this->model = $aiReport->model ?: $this->model;
        $this->userPrompt = $aiReport->prompt;
        $this->errorMessage = null;
        $this->isGenerating = false;
        $this->pendingPrompt = null;
        $this->activeTab = 'dashboard';

        if ($showNotification) {
            Notification::make()
                ->title('Laporan dimuat: ' . Str::limit($aiReport->title, 40))
                ->success()
                ->send();
        }

        $this->dispatch('report-loaded', report: $this->report);
    }

    public function deleteReport(int $id): void
    {
        AiReportModel::where('id', $id)->delete();

        if ($this->currentReportId === $id) {
            $this->newReport();
        }

        $this->loadReportsList();

        Notification::make()
            ->title('Laporan berhasil dihapus')
            ->success()
            ->send();
    }

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
        $this->isGenerating = true;
        @set_time_limit(180);

        $startTime = microtime(true);

        try {
            $tools = [];
            if ($this->enableDatabaseAccess) {
                $tools[] = new CariKaryawanDatabaseTool();
            }

            $instructions = $this->getSystemPrompt();

            $agent = new CariKaryawanAgent(
                instructions: $instructions,
                messages: [],
                tools: $tools
            );

            $selectedProvider = $this->provider;
            $selectedModel = ! empty(trim($this->model)) ? trim($this->model) : 'Content-blog';

            $response = $agent->prompt(
                prompt: $promptText,
                provider: $selectedProvider,
                model: $selectedModel,
                timeout: 150
            );

            $duration = round(microtime(true) - $startTime, 2);
            $outputText = (string) $response;

            // Fallback: If outputText is empty but a step generated text, fallback to step text
            if (empty(trim($outputText))) {
                foreach (array_reverse($response->steps ?? []) as $step) {
                    if (! empty(trim($step->text ?? ''))) {
                        $outputText = $step->text;
                        break;
                    }
                }
            }

            $promptTokens = $response->usage->promptTokens ?? 0;
            $completionTokens = $response->usage->completionTokens ?? 0;
            $totalTokens = $response->usage->totalTokens ?? ($promptTokens + $completionTokens);

            // Extract executed tool calls
            $toolCalls = [];
            foreach (($response->steps ?? []) as $step) {
                foreach (($step->toolCalls ?? []) as $tc) {
                    $toolCalls[] = [
                        'name' => $tc->name,
                        'arguments' => $tc->arguments,
                    ];
                }
            }

            // Parse report structure
            $reportData = $this->parseReportJson($outputText, $promptText);

            $this->report = $reportData;
            $this->rawText = $outputText;
            $this->toolCalls = $toolCalls;
            $this->duration = $duration;
            $this->tokens = [
                'prompt' => $promptTokens,
                'completion' => $completionTokens,
                'total' => $totalTokens,
            ];

            // Save to database
            $savedReport = AiReportModel::create([
                'user_id' => auth()->id(),
                'title' => $reportData['title'] ?? Str::limit($promptText, 60),
                'prompt' => $promptText,
                'provider' => $selectedProvider,
                'model' => $selectedModel ?? ($response->meta->model ?? 'default'),
                'report_data' => $reportData,
                'raw_text' => $outputText,
                'tool_calls' => $toolCalls,
                'duration' => $duration,
                'tokens' => $this->tokens,
            ]);

            $this->currentReportId = $savedReport->id;
            $this->loadReportsList();

            $this->dispatch('report-loaded', report: $this->report);

            Notification::make()
                ->title('Laporan eksekutif berhasil dibuat!')
                ->success()
                ->send();

        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal menghasilkan laporan: ' . $e->getMessage();
            Notification::make()
                ->title('Terjadi Kesalahan')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->isGenerating = false;
        }
    }

    protected function getSystemPrompt(): string
    {
        return <<<'PROMPT'
Kamu adalah Chief Intelligence Officer & Analis Eksekutif portal lowongan kerja CariKaryawan.
Tugas utamamu adalah menganalisis data riil dan menyusun laporan intelijen bisnis eksekutif tingkat tinggi yang interaktif, profesional, dan berbasis visual dashboard (cards, charts, tables, recommendations).

Kamu memiliki akses langsung ke database live CariKaryawan melalui tool `CariKaryawanDatabaseTool`.
PANDUAN DATABASE:
- Tabel utama:
  * `posts` (hanya lowongan kerja; id, title, slug, city_id, category_id, status, views_count, created_at, updated_at, ...)
  * `cities` (id, name, slug, province_id, ...)
  * `provinces` (id, name, slug, ...)
  * `categories` (id, name, slug, ...)
  * `page_views` (id, post_id, viewed_date, ip_address, user_agent, created_at, ...)
- Untuk menghitung views/kunjungan hari ini gunakan: `viewed_date = CURDATE()` atau `DATE(created_at) = CURDATE()`.
- WAJIB panggil tool query SQL untuk menarik data riil agar metrik dan grafik akurat sesuai isi database!

OUTPUT WAJIB:
Setelah melakukan query dan menarik data, kembalikan respon laporan akhir HANYA dalam satu blok JSON valid (diapit tanda ```json ... ```) dengan format struktur berikut:

```json
{
  "title": "Judul Laporan Eksekutif",
  "subtitle": "Penjelasan singkat ruang lingkup analisis",
  "period_or_scope": "Cakupan / Periode (misal: Seluruh Indonesia, Jawa & Bali, Hari Ini, dll)",
  "executive_summary": "Ringkasan eksekutif 2-4 paragraf yang menganalisis temuan penting, tren, dan temuan kunci dalam format Markdown.",
  "cards": [
    {
      "label": "Nama Metrik (misal: Total Loker Aktif)",
      "value": "1,840",
      "description": "Keterangan singkat metrik",
      "badge": "+15.2%",
      "badge_type": "positive",
      "color": "emerald"
    }
  ],
  "charts": [
    {
      "type": "bar",
      "title": "Judul Grafik",
      "description": "Keterangan grafik",
      "labels": ["Label A", "Label B", "Label C"],
      "datasets": [
        {
          "label": "Nama Metrik",
          "data": [120, 85, 45]
        }
      ]
    }
  ],
  "tables": [
    {
      "title": "Judul Tabel",
      "description": "Keterangan isi tabel",
      "headers": ["Kolom 1", "Kolom 2", "Kolom 3"],
      "rows": [
        ["Data A1", "Data A2", "Data A3"],
        ["Data B1", "Data B2", "Data B3"]
      ]
    }
  ],
  "recommendations": [
    "Poin rekomendasi aksi 1...",
    "Poin rekomendasi aksi 2...",
    "Poin rekomendasi aksi 3..."
  ]
}
```

ATURAN KOMPONEN FLEKSIBEL:
- Hanya sertakan komponen yang relevan dengan kebutuhan user!
- Jika user tidak membutuhkan grafik, set "charts": [].
- Jika tidak butuh tabel panjang, set "tables": [].
- Komponen "cards": tampilkan 2 sampai 4 metrik KPI penting. Nilai "color" pilih dari: "emerald", "blue", "indigo", "purple", "amber", "rose", "cyan".
- "charts": type bisa "bar", "line", "doughnut", atau "pie".
- Jangan pernah memberikan teks mentah di luar blok json, pastikan JSON valid dan dapat di-parse!
PROMPT;
    }

    protected function parseReportJson(string $text, string $fallbackPrompt): array
    {
        $cleanText = trim($text);

        // Try extracting JSON from ```json ... ``` codeblock
        if (preg_match('/```(?:json)?\s*(\{[\s\S]*?\})\s*```/i', $cleanText, $matches)) {
            $jsonString = trim($matches[1]);
            $decoded = json_decode($jsonString, true);
            if (is_array($decoded) && ! empty($decoded['title'])) {
                return $this->normalizeReportData($decoded);
            }
        }

        // Try direct JSON decode
        $decoded = json_decode($cleanText, true);
        if (is_array($decoded) && ! empty($decoded['title'])) {
            return $this->normalizeReportData($decoded);
        }

        // Fallback: If AI returned standard markdown/narrative text instead of JSON
        return [
            'title' => 'Laporan Eksekutif: ' . Str::limit($fallbackPrompt, 50),
            'subtitle' => 'Hasil analisa otomatis data CariKaryawan',
            'period_or_scope' => 'Data Terkini',
            'executive_summary' => $cleanText,
            'cards' => [],
            'charts' => [],
            'tables' => [],
            'recommendations' => [],
        ];
    }

    protected function normalizeReportData(array $data): array
    {
        return [
            'title' => $data['title'] ?? 'Laporan Eksekutif CariKaryawan',
            'subtitle' => $data['subtitle'] ?? 'Analisis Intelijen Platform',
            'period_or_scope' => $data['period_or_scope'] ?? 'Data Terkini',
            'executive_summary' => $data['executive_summary'] ?? '',
            'cards' => is_array($data['cards'] ?? null) ? $data['cards'] : [],
            'charts' => is_array($data['charts'] ?? null) ? $data['charts'] : [],
            'tables' => is_array($data['tables'] ?? null) ? $data['tables'] : [],
            'recommendations' => is_array($data['recommendations'] ?? null) ? $data['recommendations'] : [],
        ];
    }
}
