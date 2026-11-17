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
        $normalizedAddress = mb_strtoupper($address);

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

    public static function formatLocation(mixed $location, string $fallback = ''): string
    {
        if (!is_array($location)) {
            $text = trim((string) ($location ?? ''));

            return $text !== '' ? $text : $fallback;
        }

        $parts = [];
        $seen = [];
        foreach (['locality', 'city', 'area', 'raw', 'province'] as $key) {
            $val = trim((string) ($location[$key] ?? ''));
            $lower = mb_strtolower($val);
            if ($val !== '' && !isset($seen[$lower])) {
                $parts[] = $val;
                $seen[$lower] = true;
            }
        }

        return $parts !== [] ? implode(', ', $parts) : $fallback;
    }

    /**
     * Find province ID from address string
     */
    protected static function findProvinceId(string $normalizedAddress): ?int
    {
        $aliasesByProvince = [
            'ACEH' => ['NANGGROE ACEH DARUSSALAM'],
            'SUMATERA UTARA' => ['NORTH SUMATRA', 'SUMUT'],
            'SUMATERA BARAT' => ['WEST SUMATRA', 'SUMBAR'],
            'SUMATERA SELATAN' => ['SOUTH SUMATRA', 'SUMSEL'],
            'KEPULAUAN BANGKA BELITUNG' => ['BANGKA BELITUNG ISLANDS', 'KEP. BANGKA BELITUNG', 'BANGKA BELITUNG'],
            'KEPULAUAN RIAU' => ['RIAU ISLANDS', 'KEP. RIAU', 'KEPRI'],
            'DKI JAKARTA' => ['DAERAH KHUSUS IBUKOTA JAKARTA'],
            'JAWA BARAT' => ['WEST JAVA', 'JABAR'],
            'JAWA TENGAH' => ['CENTRAL JAVA', 'JATENG'],
            'DI YOGYAKARTA' => ['SPECIAL REGION OF YOGYAKARTA', 'DAERAH ISTIMEWA YOGYAKARTA', 'D.I. YOGYAKARTA', 'YOGYAKARTA'],
            'JAWA TIMUR' => ['EAST JAVA', 'JATIM'],
            'NUSA TENGGARA BARAT' => ['WEST NUSA TENGGARA', 'NTB'],
            'NUSA TENGGARA TIMUR' => ['EAST NUSA TENGGARA', 'NTT'],
            'KALIMANTAN BARAT' => ['WEST KALIMANTAN', 'KALBAR'],
            'KALIMANTAN TENGAH' => ['CENTRAL KALIMANTAN', 'KALTENG'],
            'KALIMANTAN SELATAN' => ['SOUTH KALIMANTAN', 'KALSEL'],
            'KALIMANTAN TIMUR' => ['EAST KALIMANTAN', 'KALTIM'],
            'KALIMANTAN UTARA' => ['NORTH KALIMANTAN', 'KALTARA'],
            'SULAWESI UTARA' => ['NORTH SULAWESI', 'SULUT'],
            'SULAWESI TENGAH' => ['CENTRAL SULAWESI', 'SULTENG'],
            'SULAWESI SELATAN' => ['SOUTH SULAWESI', 'SULSEL'],
            'SULAWESI TENGGARA' => ['SOUTHEAST SULAWESI', 'SULTRA'],
            'SULAWESI BARAT' => ['WEST SULAWESI', 'SULBAR'],
            'MALUKU UTARA' => ['NORTH MALUKU', 'MALUT'],
            'PAPUA BARAT' => ['WEST PAPUA'],
        ];

        $candidates = [];
        foreach (Province::all() as $province) {
            $provinceName = mb_strtoupper($province->name);
            $cleanName = trim(str_replace(['PROVINSI', 'PROV.', 'PROV'], '', $provinceName));
            $aliases = array_merge([$provinceName, $cleanName], $aliasesByProvince[$cleanName] ?? []);

            foreach (array_unique(array_filter($aliases)) as $alias) {
                $candidates[] = ['alias' => $alias, 'id' => $province->id];
            }
        }

        // Longest alias wins so KEPULAUAN RIAU beats RIAU, and word boundaries avoid BALIKPAPAN -> BALI.
        usort($candidates, fn ($a, $b) => mb_strlen($b['alias']) <=> mb_strlen($a['alias']));

        foreach ($candidates as $candidate) {
            if (preg_match('/(?<![\pL\pN])' . preg_quote($candidate['alias'], '/') . '(?![\pL\pN])/u', $normalizedAddress)) {
                return $candidate['id'];
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
        $extraAliases = [
            'JAKARTA SELATAN' => ['SOUTH JAKARTA', 'SOUTH JAKARTA CITY', 'JAKARTA SELATAN CITY'],
            'JAKARTA BARAT' => ['WEST JAKARTA', 'WEST JAKARTA CITY', 'JAKARTA BARAT CITY'],
            'JAKARTA TIMUR' => ['EAST JAKARTA', 'EAST JAKARTA CITY', 'JAKARTA TIMUR CITY'],
            'JAKARTA UTARA' => ['NORTH JAKARTA', 'NORTH JAKARTA CITY', 'JAKARTA UTARA CITY'],
            'JAKARTA PUSAT' => ['CENTRAL JAKARTA', 'CENTRAL JAKARTA CITY', 'JAKARTA PUSAT CITY'],
            'TANGERANG SELATAN' => ['SOUTH TANGERANG'],
            'BANDUNG BARAT' => ['WEST BANDUNG'],
            'PANGKAL PINANG' => ['PANGKALPINANG'],
            'TANJUNG PINANG' => ['TANJUNGPINANG'],
            'TANJUNG BALAI' => ['TANJUNGBALAI'],
            'PADANG SIDEMPUAN' => ['PADANGSIDIMPUAN'],
            'LUBUK LINGGAU' => ['LUBUKLINGGAU'],
            'TEBING TINGGI' => ['TEBINGTINGGI'],
            'GUNUNGSITOLI' => ['GUNUNG SITOLI'],
            'GUNUNGKIDUL' => ['GUNUNG KIDUL'],
            'BAU BAU' => ['BAUBAU', 'BAU-BAU'],
            'YOGYAKARTA' => ['JOGJAKARTA', 'JOGJA'],
            'KEPULAUAN SERIBU' => ['THOUSAND ISLANDS', 'KEP. SERIBU'],
        ];

        $text = mb_strtoupper($text);
        $matches = [];
        $cities = City::query()->when($provinceId, fn ($query) => $query->where('province_id', $provinceId))->get();

        foreach ($cities as $city) {
            $name = mb_strtoupper($city->name);
            $short = preg_replace('/^(?:KOTA|KABUPATEN|KAB\.)\s+/u', '', $name);
            $aliases = [$name, $short];

            if (preg_match('/^(?:KABUPATEN|KAB\.)\s+/u', $name)) {
                $aliases[] = "KABUPATEN {$short}";
                $aliases[] = "KAB. {$short}";
                $aliases[] = "KAB {$short}";
                $aliases[] = "{$short} REGENCY";
                $aliases[] = "REGENCY OF {$short}";
            } elseif (str_starts_with($name, 'KOTA ')) {
                $aliases[] = "{$short} CITY";
            }

            foreach ($extraAliases[$short] ?? [] as $extra) {
                $aliases[] = $extra;
            }

            foreach (array_unique($aliases) as $alias) {
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
