<?php

namespace App\Services;

use App\Models\ApifyAccount;
use App\Models\City;
use App\Models\Landmark;
use App\Models\Province;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;
use RuntimeException;
use Throwable;

class LandmarkScraperService
{
    /**
     * Discover landmarks for a given city using the chosen method.
     *
     * @param City $city
     * @param string $method ('ai', 'osm', 'apify')
     * @param array $options
     * @return array
     */
    public function discover(City $city, string $method = 'ai', array $options = []): array
    {
        $city->loadMissing('province');
        $limit = max(1, min((int) ($options['limit'] ?? 15), 50));
        $types = $options['types'] ?? [];

        return match ($method) {
            'ai' => $this->discoverViaAi($city, $limit, $types),
            'osm' => $this->discoverViaOsm($city, $limit, $types),
            'apify' => $this->discoverViaApify($city, $limit, $options),
            default => throw new RuntimeException("Metode scraping [{$method}] tidak didukung."),
        };
    }

    /**
     * Method 1: AI (OpenAI GPT) Structured Extraction
     */
    protected function discoverViaAi(City $city, int $limit = 15, array $types = []): array
    {
        $cityName = $city->name;
        $provinceName = $city->province?->name ?? 'Indonesia';

        $focusTypesStr = !empty($types)
            ? 'Fokuskan pada kategori: ' . implode(', ', $types) . '.'
            : 'Sertakan kombinasi lengkap: Stasiun Kereta Api Utama, Bandara, Terminal Bus Utama, Tempat Wisata Ikonik/Sejarah, Pusat Perbelanjaan Populer, Alun-Alun/Pusat Kota, dan Kampus/Universitas Terkenal.';

        $systemPrompt = <<<PROMPT
Kamu adalah sistem kurasi geospasial dan landmark terpercaya untuk Indonesia.
Tugasmu adalah menghasilkan data daftar landmark dan titik transit paling penting, strategis, dan populer di wilayah kota yang diminta.

Format output WAJIB HANYA berupa JSON Object murni yang valid tanpa markdown code fences, tanpa kata pengantar atau penutup.

Format struktur JSON:
{
  "landmarks": [
    {
      "name": "Nama Lengkap Landmark (misal: Stasiun Bandung)",
      "type": "transit_station | airport | tourist_attraction | shopping_mall | university | public_square | cultural_landmark",
      "latitude": -6.9144444,
      "longitude": 107.6025,
      "default_radius_km": 3.0,
      "description": "Deskripsi editorial informatif 2-3 kalimat mengenai landmark ini dan daya tariknya.",
      "meta_title": "Tempat Menarik di Sekitar [Nama Landmark] ([Nama Kota]) — DISEKITAR",
      "meta_description": "Rekomendasi hotel, kafe, dan kuliner terdekat dalam radius [radius] km di sekitar [Nama Landmark], [Nama Kota]."
    }
  ]
}
PROMPT;

        $userPrompt = "Berikan {$limit} landmark paling populer & strategis di {$cityName}, Provinsi {$provinceName}, Indonesia. {$focusTypesStr} Pastikan koordinat latitude dan longitude akurat.";

        try {
            $baseUri = config('openai.base_uri') ?: 'https://api.openai.com/v1';
            if (!str_starts_with($baseUri, 'http://') && !str_starts_with($baseUri, 'https://')) {
                $baseUri = 'https://' . $baseUri;
            }
            $endpoint = rtrim($baseUri, '/') . '/chat/completions';

            $apiKey = config('openai.api_key');
            $headers = [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ];

            if ($org = config('openai.organization')) {
                $headers['OpenAI-Organization'] = $org;
            }
            if ($project = config('openai.project')) {
                $headers['OpenAI-Project'] = $project;
            }

            $model = config('openai.model_landmark') ?: config('openai.model', 'gpt-4o-mini');

            $httpResponse = Http::withHeaders($headers)
                ->timeout(120)
                ->post($endpoint, [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => 0.2,
                    'stream' => false,
                ]);

            if (!$httpResponse->successful()) {
                $err = $httpResponse->json('error.message') ?? $httpResponse->body();
                throw new RuntimeException("API AI Error ({$httpResponse->status()}): " . Str::limit($err, 300));
            }

            $rawContent = $this->parseAiResponseContent($httpResponse->body());

            // 1. Clean reasoning tags if any
            $cleaned = preg_replace('/<think>[\s\S]*?<\/think>/i', '', $rawContent);
            $cleaned = preg_replace('/<thought>[\s\S]*?<\/thought>/i', '', $cleaned);

            // 2. Remove all markdown code fences
            $cleaned = preg_replace('/```(?:json)?/i', '', $cleaned);
            $cleaned = str_replace('```', '', $cleaned);

            // 3. Extract JSON array or object between first and last bracket/brace
            $firstBracket = strpos($cleaned, '[');
            $firstBrace = strpos($cleaned, '{');

            if ($firstBracket !== false && ($firstBrace === false || $firstBracket < $firstBrace)) {
                $lastBracket = strrpos($cleaned, ']');
                if ($lastBracket !== false && $lastBracket > $firstBracket) {
                    $cleaned = substr($cleaned, $firstBracket, $lastBracket - $firstBracket + 1);
                }
            } elseif ($firstBrace !== false) {
                $lastBrace = strrpos($cleaned, '}');
                if ($lastBrace !== false && $lastBrace > $firstBrace) {
                    $cleaned = substr($cleaned, $firstBrace, $lastBrace - $firstBrace + 1);
                }
            }

            // 4. Fix trailing commas before closing brackets/braces
            $cleaned = preg_replace('/,\s*([\]}])/m', '$1', $cleaned);

            $decoded = json_decode(trim($cleaned), true);

            // Normalize decoded structure
            $items = [];
            if (is_array($decoded)) {
                if (isset($decoded['landmarks']) && is_array($decoded['landmarks'])) {
                    $items = $decoded['landmarks'];
                } elseif (isset($decoded[0])) {
                    $items = $decoded;
                }
            }

            if (empty($items)) {
                $jsonErr = json_last_error_msg();
                Log::warning('Landmark AI Parse Failed', [
                    'raw' => $rawContent,
                    'cleaned' => $cleaned,
                    'json_error' => $jsonErr,
                ]);
                throw new RuntimeException("Gagal membaca struktur JSON dari response AI ({$jsonErr}): " . Str::limit($rawContent, 200));
            }

            $results = [];
            foreach ($items as $item) {
                if (empty($item['name']) || !isset($item['latitude']) || !isset($item['longitude'])) {
                    continue;
                }

                $results[] = [
                    'name' => trim((string) $item['name']),
                    'slug' => Str::slug($item['name']),
                    'type' => $item['type'] ?? 'landmark',
                    'latitude' => (float) $item['latitude'],
                    'longitude' => (float) $item['longitude'],
                    'default_radius_km' => isset($item['default_radius_km']) ? (float) $item['default_radius_km'] : 3.0,
                    'description' => $item['description'] ?? null,
                    'meta_title' => $item['meta_title'] ?? null,
                    'meta_description' => $item['meta_description'] ?? null,
                    'source' => 'ai',
                ];
            }

            return $results;
        } catch (Throwable $e) {
            Log::error('LandmarkScraperService AI Error', ['error' => $e->getMessage(), 'city' => $cityName]);
            throw new RuntimeException('Gagal generate landmark via AI: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Method 2: OpenStreetMap Overpass API
     */
    protected function discoverViaOsm(City $city, int $limit = 15, array $types = []): array
    {
        $cityName = $city->name;
        
        // Clean City Name (e.g. "Kota Bandung" -> "Bandung", "Kabupaten Sleman" -> "Sleman")
        $cleanCity = trim(preg_replace('/^(Kota|Kabupaten|Kab\.)\s+/i', '', $cityName));

        // Overpass QL Query searching for key POIs in the city area
        $overpassQuery = <<<QL
[out:json][timeout:25];
area["name"~"{$cleanCity}",i]["admin_level"~"^(4|5|6|7|8)$"]->.searchArea;
(
  node["railway"="station"](area.searchArea);
  node["aeroway"="aerodrome"](area.searchArea);
  node["amenity"="bus_station"](area.searchArea);
  node["tourism"~"^(attraction|theme_park|viewpoint|museum)$"](area.searchArea);
  node["shop"="mall"](area.searchArea);
  node["amenity"="university"](area.searchArea);
  node["historic"~"^(monument|memorial|castle|fort)$"](area.searchArea);
  node["leisure"="park"]["name"](area.searchArea);
);
out center {$limit};
QL;

        try {
            $response = Http::timeout(30)
                ->asForm()
                ->post('https://overpass-api.de/api/interpreter', [
                    'data' => $overpassQuery,
                ]);

            if (!$response->successful()) {
                // Fallback to nominatim search if overpass fails
                return $this->discoverViaNominatim($city, $limit);
            }

            $data = $response->json();
            $elements = $data['elements'] ?? [];

            if (empty($elements)) {
                return $this->discoverViaNominatim($city, $limit);
            }

            $results = [];
            foreach ($elements as $el) {
                $tags = $el['tags'] ?? [];
                $name = $tags['name'] ?? null;
                if (!$name) {
                    continue;
                }

                $lat = $el['lat'] ?? ($el['center']['lat'] ?? null);
                $lon = $el['lon'] ?? ($el['center']['lon'] ?? null);

                if (!$lat || !$lon) {
                    continue;
                }

                $type = 'landmark';
                $radius = 3.0;

                if (isset($tags['railway']) && $tags['railway'] === 'station') {
                    $type = 'transit_station';
                    $radius = 2.5;
                } elseif (isset($tags['aeroway'])) {
                    $type = 'airport';
                    $radius = 5.0;
                } elseif (isset($tags['amenity']) && $tags['amenity'] === 'bus_station') {
                    $type = 'transit_station';
                    $radius = 2.0;
                } elseif (isset($tags['tourism'])) {
                    $type = 'tourist_attraction';
                    $radius = 3.0;
                } elseif (isset($tags['shop']) && $tags['shop'] === 'mall') {
                    $type = 'shopping_mall';
                    $radius = 2.0;
                } elseif (isset($tags['amenity']) && $tags['amenity'] === 'university') {
                    $type = 'university';
                    $radius = 2.5;
                }

                $results[] = [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'type' => $type,
                    'latitude' => (float) $lat,
                    'longitude' => (float) $lon,
                    'default_radius_km' => $radius,
                    'description' => "Landmark {$name} berlokasi di {$city->name}, menjadi salah satu titik penting dan pusat aktivitas di wilayah ini.",
                    'meta_title' => "Tempat Menarik di Sekitar {$name} ({$city->name}) — DISEKITAR",
                    'meta_description' => "Rekomendasi hotel, kafe, dan kuliner terdekat dalam radius {$radius} km di dekat {$name}, {$city->name}.",
                    'source' => 'osm',
                ];

                if (count($results) >= $limit) {
                    break;
                }
            }

            return $results;
        } catch (Throwable $e) {
            Log::warning('OSM Overpass query failed, trying Nominatim fallback', ['error' => $e->getMessage()]);
            return $this->discoverViaNominatim($city, $limit);
        }
    }

    /**
     * Fallback Nominatim Search for OpenStreetMap
     */
    protected function discoverViaNominatim(City $city, int $limit = 15): array
    {
        $cleanCity = trim(preg_replace('/^(Kota|Kabupaten|Kab\.)\s+/i', '', $city->name));
        $queries = [
            "Stasiun di {$cleanCity}",
            "Tempat Wisata di {$cleanCity}",
            "Mall di {$cleanCity}",
            "Alun-alun {$cleanCity}",
        ];

        $results = [];
        $seen = [];

        foreach ($queries as $q) {
            if (count($results) >= $limit) {
                break;
            }

            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'DisekitarApp/1.0 (info@disekitar.com)',
                ])->timeout(10)->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $q,
                    'format' => 'json',
                    'limit' => 5,
                    'countrycodes' => 'id',
                ]);

                if ($response->successful()) {
                    foreach ($response->json() as $item) {
                        $name = explode(',', $item['display_name'])[0] ?? null;
                        if (!$name || isset($seen[Str::slug($name)])) {
                            continue;
                        }

                        $seen[Str::slug($name)] = true;
                        $results[] = [
                            'name' => $name,
                            'slug' => Str::slug($name),
                            'type' => 'landmark',
                            'latitude' => (float) $item['lat'],
                            'longitude' => (float) $item['lon'],
                            'default_radius_km' => 3.0,
                            'description' => "Landmark {$name} di {$city->name}.",
                            'meta_title' => "Tempat Menarik di Sekitar {$name} ({$city->name}) — DISEKITAR",
                            'meta_description' => "Rekomendasi hotel, kafe, dan kuliner terdekat dalam radius 3 km di sekitar {$name}, {$city->name}.",
                            'source' => 'osm_nominatim',
                        ];
                    }
                }
            } catch (Throwable) {
                // ignore
            }
        }

        return $results;
    }

    /**
     * Method 3: Apify Google Places Scraper
     */
    protected function discoverViaApify(City $city, int $limit = 15, array $options = []): array
    {
        $account = ApifyAccount::active()->first();
        if (!$account || empty($account->api_token)) {
            throw new RuntimeException("Tidak ada akun Apify aktif dengan API Token yang valid. Silakan atur di menu Apify Accounts.");
        }

        $apifyService = app(ApifyScraperService::class);
        $cityName = $city->name;
        $query = "Landmark populer, stasiun, mall, dan tempat wisata di {$cityName}";

        // Start crawler synchronously or wait for items
        $runData = $apifyService->startGooglePlacesCrawler(
            token: $account->api_token,
            query: $query,
            location: "{$cityName}, Indonesia",
            maxPlaces: $limit,
            includeImages: false,
            includeReviews: false
        );

        $datasetId = $runData['datasetId'] ?? null;
        $runId = $runData['runId'] ?? null;

        if (!$runId) {
            throw new RuntimeException("Gagal memulai crawler Apify Google Places.");
        }

        // Wait up to 60 seconds for Apify run to complete
        $statusData = $apifyService->waitForRunCompletion($runId, $account->api_token, 60, 4);
        $status = $statusData['status'] ?? 'UNKNOWN';

        if ($status !== 'SUCCEEDED') {
            throw new RuntimeException("Apify crawler run berakhir dengan status {$status}.");
        }

        $items = $apifyService->fetchDatasetItems($datasetId ?: $statusData['datasetId'], $account->api_token, $limit);

        if (empty($items)) {
            throw new RuntimeException("Tidak ada data landmark yang berhasil didapatkan dari Apify untuk {$cityName}.");
        }

        $results = [];
        foreach ($items as $item) {
            $name = $item['title'] ?? ($item['name'] ?? null);
            $location = $item['location'] ?? [];
            $lat = $location['lat'] ?? ($item['latitude'] ?? null);
            $lng = $location['lng'] ?? ($item['longitude'] ?? null);

            if (!$name || !$lat || !$lng) {
                continue;
            }

            $categoryName = $item['categoryName'] ?? ($item['category'] ?? 'Landmark');
            $type = 'tourist_attraction';
            if (stripos($categoryName, 'station') !== false || stripos($categoryName, 'stasiun') !== false) {
                $type = 'transit_station';
            } elseif (stripos($categoryName, 'airport') !== false || stripos($categoryName, 'bandara') !== false) {
                $type = 'airport';
            } elseif (stripos($categoryName, 'mall') !== false || stripos($categoryName, 'shopping') !== false) {
                $type = 'shopping_mall';
            }

            $results[] = [
                'name' => $name,
                'slug' => Str::slug($name),
                'type' => $type,
                'latitude' => (float) $lat,
                'longitude' => (float) $lng,
                'default_radius_km' => 3.0,
                'description' => "{$name} adalah {$categoryName} populer yang berlokasi di {$city->name}.",
                'meta_title' => "Tempat Menarik di Sekitar {$name} ({$city->name}) — DISEKITAR",
                'meta_description' => "Rekomendasi hotel, kafe, dan kuliner terdekat dalam radius 3 km di sekitar {$name}, {$city->name}.",
                'source' => 'apify',
            ];
        }

        return $results;
    }

    /**
     * Save/Persist discovered landmarks into database.
     *
     * @param City $city
     * @param array $landmarks
     * @param bool $skipExisting
     * @return array ['saved' => int, 'updated' => int, 'skipped' => int]
     */
    public function saveLandmarks(City $city, array $landmarks, bool $skipExisting = true): array
    {
        $city->loadMissing('province');
        $saved = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($landmarks as $item) {
            $name = trim($item['name'] ?? '');
            if (!$name) {
                continue;
            }

            $baseSlug = Str::slug($item['slug'] ?? $name);
            if (!$baseSlug) {
                $baseSlug = 'landmark-' . Str::random(6);
            }

            $existing = Landmark::where('city_id', $city->id)
                ->where(function ($q) use ($name, $baseSlug) {
                    $q->where('slug', $baseSlug)
                      ->orWhere('name', $name);
                })
                ->first();

            if ($existing) {
                if ($skipExisting) {
                    $skipped++;
                    continue;
                }

                $existing->update([
                    'type' => $item['type'] ?? $existing->type,
                    'latitude' => $item['latitude'] ?? $existing->latitude,
                    'longitude' => $item['longitude'] ?? $existing->longitude,
                    'default_radius_km' => $item['default_radius_km'] ?? $existing->default_radius_km,
                    'description' => $item['description'] ?? $existing->description,
                    'meta_title' => $item['meta_title'] ?? $existing->meta_title,
                    'meta_description' => $item['meta_description'] ?? $existing->meta_description,
                ]);
                $updated++;
                continue;
            }

            // Ensure unique slug across table
            $slug = $baseSlug;
            $counter = 1;
            while (Landmark::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            Landmark::create([
                'province_id' => $city->province_id,
                'city_id' => $city->id,
                'name' => $name,
                'slug' => $slug,
                'type' => $item['type'] ?? 'landmark',
                'latitude' => $item['latitude'],
                'longitude' => $item['longitude'],
                'default_radius_km' => $item['default_radius_km'] ?? 3.0,
                'description' => $item['description'] ?? null,
                'meta_title' => $item['meta_title'] ?? null,
                'meta_description' => $item['meta_description'] ?? null,
            ]);

            $saved++;
        }

        return [
            'saved' => $saved,
            'updated' => $updated,
            'skipped' => $skipped,
            'total' => count($landmarks),
        ];
    }

    /**
     * Extract full content text from standard OpenAI JSON response or SSE text/event-stream chunks.
     */
    public function parseAiResponseContent(string $rawBody): string
    {
        $rawBody = trim($rawBody);
        if ($rawBody === '') {
            return '';
        }

        // Standard single JSON payload
        if (str_starts_with($rawBody, '{')) {
            $json = json_decode($rawBody, true);
            if (isset($json['choices'][0]['message']['content'])) {
                return (string) $json['choices'][0]['message']['content'];
            }
            if (isset($json['choices'][0]['text'])) {
                return (string) $json['choices'][0]['text'];
            }
        }

        // SSE text/event-stream chunks (data: {...}\n\ndata: {...})
        $assembled = '';
        $lines = preg_split('/\r\n|\r|\n/', $rawBody);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!str_starts_with($line, 'data:')) {
                continue;
            }
            $dataStr = trim(substr($line, 5));
            if ($dataStr === '[DONE]' || $dataStr === '') {
                continue;
            }
            $chunk = json_decode($dataStr, true);
            if (!is_array($chunk)) {
                continue;
            }
            if (isset($chunk['choices'][0]['delta']['content'])) {
                $assembled .= $chunk['choices'][0]['delta']['content'];
            } elseif (isset($chunk['choices'][0]['text'])) {
                $assembled .= $chunk['choices'][0]['text'];
            } elseif (isset($chunk['choices'][0]['message']['content'])) {
                $assembled .= $chunk['choices'][0]['message']['content'];
            }
        }

        return trim($assembled);
    }
}

