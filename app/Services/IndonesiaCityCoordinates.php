<?php

namespace App\Services;

class IndonesiaCityCoordinates
{
    /**
     * Daftar koordinat kota/wilayah populer di Indonesia
     */
    protected static array $cities = [
        'jakarta' => ['lat' => -6.2088, 'lon' => 106.8456],
        'jakarta pusat' => ['lat' => -6.1818, 'lon' => 106.8223],
        'jakarta selatan' => ['lat' => -6.2615, 'lon' => 106.8106],
        'jakarta barat' => ['lat' => -6.1683, 'lon' => 106.7589],
        'jakarta utara' => ['lat' => -6.1384, 'lon' => 106.8640],
        'jakarta timur' => ['lat' => -6.2250, 'lon' => 106.9004],
        'bandung' => ['lat' => -6.9175, 'lon' => 107.6191],
        'bandung barat' => ['lat' => -6.8439, 'lon' => 107.4984],
        'surabaya' => ['lat' => -7.2575, 'lon' => 112.7521],
        'semarang' => ['lat' => -6.9667, 'lon' => 110.4167],
        'yogyakarta' => ['lat' => -7.7956, 'lon' => 110.3695],
        'sleman' => ['lat' => -7.7167, 'lon' => 110.3556],
        'bantul' => ['lat' => -7.8833, 'lon' => 110.3333],
        'kulon progo' => ['lat' => -7.7667, 'lon' => 110.1667],
        'gunungkidul' => ['lat' => -7.9667, 'lon' => 110.6000],
        'solo' => ['lat' => -7.5667, 'lon' => 110.8167],
        'surakarta' => ['lat' => -7.5667, 'lon' => 110.8167],
        'malang' => ['lat' => -7.9797, 'lon' => 112.6304],
        'batu' => ['lat' => -7.8700, 'lon' => 112.5268],
        'bogor' => ['lat' => -6.5971, 'lon' => 106.8060],
        'depok' => ['lat' => -6.4025, 'lon' => 106.7942],
        'tangerang' => ['lat' => -6.1783, 'lon' => 106.6319],
        'tangerang selatan' => ['lat' => -6.2889, 'lon' => 106.7181],
        'bekasi' => ['lat' => -6.2383, 'lon' => 106.9756],
        'cirebon' => ['lat' => -6.7063, 'lon' => 108.5570],
        'sukabumi' => ['lat' => -6.9277, 'lon' => 106.9300],
        'denpasar' => ['lat' => -8.6705, 'lon' => 115.2126],
        'badung' => ['lat' => -8.5833, 'lon' => 115.1833],
        'gianyar' => ['lat' => -8.5444, 'lon' => 115.3267],
        'tabanan' => ['lat' => -8.5411, 'lon' => 115.1253],
        'buleleng' => ['lat' => -8.1167, 'lon' => 115.0833],
        'medan' => ['lat' => 3.5952, 'lon' => 98.6722],
        'padang' => ['lat' => -0.9471, 'lon' => 100.4172],
        'pekanbaru' => ['lat' => 0.5071, 'lon' => 101.4478],
        'palembang' => ['lat' => -2.9761, 'lon' => 104.7754],
        'bandar lampung' => ['lat' => -5.4500, 'lon' => 105.2667],
        'lampung' => ['lat' => -5.4500, 'lon' => 105.2667],
        'batam' => ['lat' => 1.1301, 'lon' => 104.0529],
        'pontianak' => ['lat' => -0.0263, 'lon' => 109.3425],
        'banjarmasin' => ['lat' => -3.3167, 'lon' => 114.5900],
        'balikpapan' => ['lat' => -1.2379, 'lon' => 116.8529],
        'samarinda' => ['lat' => -0.5022, 'lon' => 117.1536],
        'makassar' => ['lat' => -5.1477, 'lon' => 119.4327],
        'manado' => ['lat' => 1.4748, 'lon' => 124.8421],
        'mataram' => ['lat' => -8.5833, 'lon' => 116.1167],
        'lombok barat' => ['lat' => -8.6833, 'lon' => 116.1333],
        'kupang' => ['lat' => -10.1772, 'lon' => 123.6070],
        'ambon' => ['lat' => -3.6554, 'lon' => 128.1906],
        'jayapura' => ['lat' => -2.5337, 'lon' => 140.7181],
        'serang' => ['lat' => -6.1200, 'lon' => 106.1500],
        'cilegon' => ['lat' => -6.0022, 'lon' => 106.0150],
        'magelang' => ['lat' => -7.4797, 'lon' => 110.2178],
        'purwokerto' => ['lat' => -7.4244, 'lon' => 109.2300],
        'banyumas' => ['lat' => -7.5167, 'lon' => 109.2833],
        'tegal' => ['lat' => -6.8694, 'lon' => 109.1403],
        'pekalongan' => ['lat' => -6.8886, 'lon' => 109.6753],
        'kudus' => ['lat' => -6.8048, 'lon' => 110.8405],
        'jember' => ['lat' => -8.1724, 'lon' => 113.7007],
        'banyuwangi' => ['lat' => -8.2192, 'lon' => 114.3691],
        'kediri' => ['lat' => -7.8167, 'lon' => 112.0167],
        'madiun' => ['lat' => -7.6298, 'lon' => 111.5239],
        'probolinggo' => ['lat' => -7.7543, 'lon' => 113.2159],
        'pasuruan' => ['lat' => -7.6453, 'lon' => 112.9075],
        'sidoarjo' => ['lat' => -7.4478, 'lon' => 112.7183],
        'gresik' => ['lat' => -7.1566, 'lon' => 112.6555],
    ];

    /**
     * Cari koordinat berdasarkan nama kota
     */
    public static function getCoordinates(?string $cityName): ?array
    {
        if (empty($cityName)) {
            return null;
        }

        $clean = strtolower(trim($cityName));
        $clean = preg_replace('/^(kota|kabupaten|kab\.|dki)\s+/i', '', $clean);
        $clean = trim($clean);

        if (isset(self::$cities[$clean])) {
            return self::$cities[$clean];
        }

        // Coba pencarian sebagian (partial match)
        foreach (self::$cities as $key => $coords) {
            if (str_contains($clean, $key) || str_contains($key, $clean)) {
                return $coords;
            }
        }

        return ['lat' => -6.2088, 'lon' => 106.8456]; // Default Jakarta
    }

    public static function getLat(?string $cityName): float
    {
        return self::getCoordinates($cityName)['lat'] ?? -6.2088;
    }

    public static function getLon(?string $cityName): float
    {
        return self::getCoordinates($cityName)['lon'] ?? 106.8456;
    }
}
