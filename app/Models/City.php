<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $guarded = [
        'id',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function navigation(): \Illuminate\Database\Eloquent\Collection
    {
        $ids = json_decode((string) Setting::get('nav_city_ids', '[]'), true);
        $ids = is_array($ids) ? array_values(array_unique(array_filter($ids, fn ($id) => (is_int($id) || is_string($id)) && ctype_digit((string) $id) && (int) $id > 0))) : [];
        $ids = array_slice(array_map('intval', $ids), 0, 8);

        return static::query()->whereIn('id', $ids)->get()
            ->sortBy(fn ($city) => array_search($city->id, $ids, true))->values();
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
