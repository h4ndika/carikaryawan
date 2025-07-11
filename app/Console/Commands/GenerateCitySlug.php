<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\City;

class GenerateCitySlug extends Command
{
    protected $signature = 'generate:city-slug';
    protected $description = 'Generate slug for all cities based on name with type (kota/kabupaten)';

    public function handle()
    {
        $cities = City::all();

        foreach ($cities as $city) {
            $name = strtoupper($city->name);

            // Deteksi jenis
            if (str_contains($name, 'KOTA')) {
                $type = 'kota';
                $cleanName = trim(str_ireplace('KOTA', '', $city->name));
            } elseif (str_contains($name, 'KAB.')) {
                $type = 'kabupaten';
                $cleanName = trim(str_ireplace('KAB.', '', $city->name));
            } else {
                $type = 'other';
                $cleanName = $city->name;
            }

            // Buat slug
            $slug = Str::slug($cleanName . '-' . $type);

            $city->slug = $slug;
            $city->save();

            $this->info("Updated: {$city->name} → {$slug}");
        }

        $this->info('✔ All city slugs generated!');
        return Command::SUCCESS;
    }
}
