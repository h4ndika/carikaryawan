<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\Province;

class GenerateProvinceSlug extends Command
{
    protected $signature = 'generate:province-slug';
    protected $description = 'Generate slug for all provinces based on name';

    public function handle()
    {
        $provinces = Province::all();

        foreach ($provinces as $province) {
            $slug = Str::slug($province->name);

            $province->slug = $slug;
            $province->save();

            $this->info("Updated: {$province->name} → {$slug}");
        }

        $this->info('✔ All province slugs generated!');
        return Command::SUCCESS;
    }
}
