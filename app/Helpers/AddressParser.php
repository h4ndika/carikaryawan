<?php

namespace App\Helpers;

use App\Models\City;
use App\Models\Province;
use Illuminate\Support\Str;

class AddressParser
{
    /**
     * Parse Indonesian address string to extract province_id and city_id
     *
     * @param string $address Full address string from Google Maps
     * @return array ['province_id' => int|null, 'city_id' => int|null]
     */
    public static function parse(string $address): array
    {
        if (empty($address)) {
            return ['province_id' => null, 'city_id' => null];
        }

        $provinceId = null;
        $cityId = null;

        // Normalize address
        $normalizedAddress = strtoupper($address);

        // Try to find province
        $provinceId = self::findProvinceId($normalizedAddress);

        // Try to find city
        $cityId = self::findCityId($normalizedAddress, $provinceId);

        // If city found but no province, get province from city
        if ($cityId && !$provinceId) {
            $city = City::find($cityId);
            if ($city) {
                $provinceId = $city->province_id;
            }
        }

        return [
            'province_id' => $provinceId,
            'city_id' => $cityId,
        ];
    }

    /**
     * Find province ID from address string
     */
    protected static function findProvinceId(string $normalizedAddress): ?int
    {
        // Get all provinces
        $provinces = Province::all();

        foreach ($provinces as $province) {
            $provinceName = strtoupper($province->name);
            
            // Direct match
            if (str_contains($normalizedAddress, $provinceName)) {
                return $province->id;
            }

            // Try without common prefixes/suffixes
            $cleanName = str_replace(['PROVINSI', 'PROV.', 'PROV'], '', $provinceName);
            $cleanName = trim($cleanName);
            
            if (!empty($cleanName) && str_contains($normalizedAddress, $cleanName)) {
                return $province->id;
            }
        }

        return null;
    }

    /**
     * Find city ID from address string
     */
    protected static function findCityId(string $normalizedAddress, ?int $provinceId = null): ?int
    {
        $ids = self::matchingCityIds($normalizedAddress, $provinceId);

        return count($ids) === 1 ? $ids[0] : null;
    }

    public static function matchingCityIds(string $text, ?int $provinceId = null): array
    {
        $text = mb_strtoupper($text);
        $matches = [];
        $cities = City::query()->when($provinceId, fn ($query) => $query->where('province_id', $provinceId))->get();

        foreach ($cities as $city) {
            $name = mb_strtoupper($city->name);
            $short = preg_replace('/^(?:KOTA|KABUPATEN|KAB\.)\s+/u', '', $name);
            foreach (array_unique([$name, $short]) as $alias) {
                if ($alias === '') {
                    continue;
                }
                preg_match_all('/(?<![\pL\pN])' . preg_quote($alias, '/') . '(?![\pL\pN])/u', $text, $hits, PREG_OFFSET_CAPTURE);
                foreach ($hits[0] as [$value, $offset]) {
                    $matches[] = ['id' => $city->id, 'start' => $offset, 'end' => $offset + strlen($value)];
                }
            }
        }

        // Prefer the full place name at each occurrence; separate cities remain ambiguous.
        $matches = array_filter($matches, function ($match) use ($matches) {
            foreach ($matches as $other) {
                if ($other['start'] <= $match['start'] && $other['end'] >= $match['end']
                    && ($other['start'] < $match['start'] || $other['end'] > $match['end'])) {
                    return false;
                }
            }

            return true;
        });

        return array_values(array_unique(array_column($matches, 'id')));
    }

    /**
     * Extract keywords from address for better matching
     */
    protected static function extractKeywords(string $address): array
    {
        // Split by common separators
        $parts = preg_split('/[,\.\-\/]/', $address);
        
        // Clean and filter
        $keywords = array_map('trim', $parts);
        $keywords = array_filter($keywords, function($keyword) {
            return strlen($keyword) > 2; // Only words longer than 2 chars
        });

        return array_values($keywords);
    }
}
