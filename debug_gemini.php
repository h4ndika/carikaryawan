<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Laravel\Ai\Promptable;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Attributes\MaxSteps;
use App\Ai\Tools\DisekitarDatabaseTool;

#[MaxSteps(10)]
class TestCustomAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    public function __construct(public string $instructions, public iterable $messages, public iterable $tools) {}
    public function instructions(): string { return $this->instructions; }
    public function messages(): iterable { return $this->messages; }
    public function tools(): iterable { return $this->tools; }
    public function maxSteps(): int { return 10; }
}

$tool = new DisekitarDatabaseTool();
$instructions = "Kamu adalah analis data cerdas untuk direktori DiSekitar. Gunakan tool database yang tersedia untuk memeriksa data riil dan jawab dengan angka/informasi akurat. JANGAN hanya menyajikan perintah query SQL mentah kecuali diminta secara eksplisit.\n\nKamu memiliki akses langsung ke database live DiSekitar melalui tool database yang tersedia. Jika pertanyaan berkaitan dengan jumlah postingan, data kota, kategori, landmarks, page_views, rating, atau statistik lainnya, WAJIB panggil tool untuk mengecek data riil (khusus page_views hari ini gunakan kolom viewed_date = CURDATE() atau DATE(created_at) = CURDATE(), bukan visited_at). Jawab langsung hasilnya dalam bahasa Indonesia yang ramah, informatif, dan akurat dalam format Markdown. JANGAN hanya menyajikan perintah query SQL mentah kecuali diminta secara eksplisit.";

$agent = new TestCustomAgent(
    instructions: $instructions,
    messages: [],
    tools: [$tool]
);

try {
    $promptText = "kira kira kota mana yang menjadi prioritas untuk di boosting?";
    echo "Prompting agent with provider: openai-compatible, model: Gemini...\n";
    $response = $agent->prompt(
        prompt: $promptText,
        provider: 'openai-compatible',
        model: 'Gemini',
        timeout: 120
    );

    echo "TEXT: [" . (string)$response . "]\n";
    echo "STEPS: " . count($response->steps ?? []) . "\n";
    foreach ($response->steps ?? [] as $i => $step) {
        echo "=== STEP $i ===\n";
        echo "Step text: [" . $step->text . "]\n";
        echo "Finish reason: " . ($step->finishReason->value ?? $step->finishReason ?? 'none') . "\n";
        echo "Tool calls count: " . count($step->toolCalls ?? []) . "\n";
        foreach ($step->toolCalls ?? [] as $tc) {
            echo "  TC: " . $tc->name . " -> " . json_encode($tc->arguments) . "\n";
        }
        echo "Tool results count: " . count($step->toolResults ?? []) . "\n";
        foreach ($step->toolResults ?? [] as $tr) {
            echo "  TR: " . substr(json_encode($tr->result), 0, 300) . "\n";
        }
    }
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
