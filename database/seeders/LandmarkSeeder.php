<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Landmark;
use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LandmarkSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = Province::query()->pluck('id', 'slug');
        $cities = City::query()->pluck('id', 'slug');

        $landmarks = [
            // --- Bandung (KOTA BANDUNG) ---
            [
                'city_slug'    => 'bandung-kota',
                'name'         => 'Stasiun Bandung',
                'type'         => 'station',
                'latitude'     => -6.9147444,
                'longitude'    => 107.6021843,
                'description'  => 'Stasiun kereta api utama Kota Bandung, pintu gerbang kedatangan dari Jakarta dan kota lain.',
                'meta_title'   => 'Stasiun Bandung — Pusat Transportasi Kereta di Kota Bandung',
                'meta_description' => 'Informasi lengkap Stasiun Bandung: jadwal, lokasi, dan tempat wisata di sekitarnya.',
            ],
            [
                'city_slug'    => 'bandung-kota',
                'name'         => 'Alun-Alun Bandung',
                'type'         => 'tourist_attraction',
                'latitude'     => -6.9217166,
                'longitude'    => 107.6064378,
                'description'  => 'Alun-alun ikonik Kota Bandung, dikelilingi Masjid Raya Bandung dan Museum Mandala Wangsit Siliwangi.',
            ],
            [
                'city_slug'    => 'bandung-kota',
                'name'         => 'Bandara Husein Sastranegara',
                'type'         => 'airport',
                'latitude'     => -6.9006514,
                'longitude'    => 107.5765841,
                'description'  => 'Bandara internasional yang melayani penerbangan domestik di Kota Bandung.',
            ],
            [
                'city_slug'    => 'bandung-kota',
                'name'         => 'Terminal Leuwipanjang',
                'type'         => 'transit_hub',
                'latitude'     => -6.9380162,
                'longitude'    => 107.5718912,
                'description'  => 'Terminal bus utama Kota Bandung, melayani antar kota antar provinsi (AKAP).',
            ],
            [
                'city_slug'    => 'bandung-kota',
                'name'         => 'Gedung Sate',
                'type'         => 'tourist_attraction',
                'latitude'     => -6.9122619,
                'longitude'    => 107.6193013,
                'description'  => 'Gedung pusat pemerintahan Provinsi Jawa Barat yang ikonik dengan sate di puncaknya.',
            ],

            // --- Jakarta Pusat ---
            [
                'city_slug'    => 'jakarta-pusat-kota',
                'name'         => 'Stasiun Gambir',
                'type'         => 'station',
                'latitude'     => -6.1774277,
                'longitude'    => 106.8305727,
                'description'  => 'Stasiun kereta api utama di pusat Jakarta, melayani perjalanan jarak jauh dan menengah.',
                'meta_title'   => 'Stasiun Gambir — Gerbang Utama Transportasi Kereta di Jakarta',
            ],
            [
                'city_slug'    => 'jakarta-pusat-kota',
                'name'         => 'Monumen Nasional (Monas)',
                'type'         => 'tourist_attraction',
                'latitude'     => -6.1753924,
                'longitude'    => 106.8271528,
                'description'  => 'Monumen ikonik Jakarta setinggi 132 meter, simbol kemerdekaan Indonesia.',
            ],
            [
                'city_slug'    => 'jakarta-pusat-kota',
                'name'         => 'Stasiun Juanda',
                'type'         => 'station',
                'latitude'     => -6.1666943,
                'longitude'    => 106.8307064,
                'description'  => 'Stasiun KRL Commuter Line di pusat Jakarta, melayani perjalanan commuter Jabodetabek.',
            ],

            // --- Jakarta Utara ---
            [
                'city_slug'    => 'jakarta-utara-kota',
                'name'         => 'Pelabuhan Tanjung Priok',
                'type'         => 'port',
                'latitude'     => -6.1048619,
                'longitude'    => 106.8802337,
                'description'  => 'Pelabuhan terbesar dan tersibuk di Indonesia, pintu gerbang perdagangan laut Jakarta.',
            ],

            // --- Jakarta Selatan ---
            [
                'city_slug'    => 'jakarta-selatan-kota',
                'name'         => 'Stasiun MRT Blok M',
                'type'         => 'station',
                'latitude'     => -6.2440604,
                'longitude'    => 106.7995317,
                'description'  => 'Stasiun MRT Jakarta di kawasan Blok M, pusat perbelanjaan dan hiburan Jakarta Selatan.',
            ],

            // --- Surabaya ---
            [
                'city_slug'    => 'surabaya-kota',
                'name'         => 'Stasiun Surabaya Gubeng',
                'type'         => 'station',
                'latitude'     => -7.2630157,
                'longitude'    => 112.7526951,
                'description'  => 'Stasiun kereta api utama Kota Surabaya, melayani perjalanan jarak jauh di Jawa Timur dan sekitarnya.',
            ],
            [
                'city_slug'    => 'surabaya-kota',
                'name'         => 'Bandara Internasional Juanda',
                'type'         => 'airport',
                'latitude'     => -7.3797098,
                'longitude'    => 112.7865375,
                'description'  => 'Bandara internasional utama Kota Surabaya, melayani penerbangan domestik dan internasional.',
            ],
            [
                'city_slug'    => 'surabaya-kota',
                'name'         => 'Tugu Pahlawan',
                'type'         => 'tourist_attraction',
                'latitude'     => -7.2456100,
                'longitude'    => 112.7377800,
                'description'  => 'Monumen bersejarah di pusat Kota Surabaya untuk memperingati Pertempuran 10 November 1945.',
            ],

            // --- Yogyakarta ---
            [
                'city_slug'    => 'yogyakarta-kota',
                'name'         => 'Stasiun Yogyakarta Tugu',
                'type'         => 'station',
                'latitude'     => -7.7892339,
                'longitude'    => 110.3634427,
                'description'  => 'Stasiun kereta api utama Kota Yogyakarta, melayani kereta jarak jauh dan commuter.',
            ],
            [
                'city_slug'    => 'yogyakarta-kota',
                'name'         => 'Bandara Internasional Adisucipto',
                'type'         => 'airport',
                'latitude'     => -7.7881687,
                'longitude'    => 110.4317580,
                'description'  => 'Bandara internasional di Yogyakarta, melayani rute domestik dan sebagian internasional.',
            ],
            [
                'city_slug'    => 'yogyakarta-kota',
                'name'         => 'Titik Nol Kilometer Yogyakarta',
                'type'         => 'tourist_attraction',
                'latitude'     => -7.8011940,
                'longitude'    => 110.3669900,
                'description'  => 'Titik pusat Kota Yogyakarta, tempat berkumpulnya berbagai jalur Malioboro dan Kraton.',
            ],
        ];

        foreach ($landmarks as $row) {
            if (!isset($cities[$row['city_slug']])) {
                continue;
            }
            $cityId = $cities[$row['city_slug']];
            $city = City::find($cityId);
            $provinceId = $city?->province_id;

            Landmark::updateOrCreate(
                ['slug' => Str::slug($row['name'])],
                [
                    'province_id'       => $provinceId,
                    'city_id'           => $cityId,
                    'name'              => $row['name'],
                    'type'              => $row['type'],
                    'latitude'          => $row['latitude'],
                    'longitude'         => $row['longitude'],
                    'default_radius_km' => $row['default_radius_km'] ?? 3,
                    'description'       => $row['description'] ?? null,
                    'meta_title'        => $row['meta_title'] ?? null,
                    'meta_description'  => $row['meta_description'] ?? null,
                ]
            );
        }
    }
}
