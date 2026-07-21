<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Services\RestCountriesService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        // Mencoba mengambil data negara dari REST Countries API terlebih dahulu
        try {
            $service = new RestCountriesService();
            $total = $service->syncAllCountries();
            if ($total > 0) {
                Log::info("CountrySeeder: Berhasil mensinkronisasi {$total} negara dari API.");
                return;
            }
        } catch (\Throwable $e) {
            Log::warning('CountrySeeder: Gagal mensinkronisasi negara dari API, menggunakan data hardcoded. Error: ' . $e->getMessage());
        }

        // Data fallback jika API tidak aktif atau gagal
        $countries = [
            ['name' => 'Indonesia', 'code' => 'IDN', 'iso2' => 'ID', 'iso3' => 'IDN', 'currency_code' => 'IDR', 'currency_name' => 'Indonesian Rupiah'],
            ['name' => 'Singapore', 'code' => 'SGP', 'iso2' => 'SG', 'iso3' => 'SGP', 'currency_code' => 'SGD', 'currency_name' => 'Singapore Dollar'],
            ['name' => 'Malaysia', 'code' => 'MYS', 'iso2' => 'MY', 'iso3' => 'MYS', 'currency_code' => 'MYR', 'currency_name' => 'Malaysian Ringgit'],
            ['name' => 'China', 'code' => 'CHN', 'iso2' => 'CN', 'iso3' => 'CHN', 'currency_code' => 'CNY', 'currency_name' => 'Chinese Yuan'],
            ['name' => 'South Korea', 'code' => 'KOR', 'iso2' => 'KR', 'iso3' => 'KOR', 'currency_code' => 'KRW', 'currency_name' => 'South Korean Won'],
            ['name' => 'Japan', 'code' => 'JPN', 'iso2' => 'JP', 'iso3' => 'JPN', 'currency_code' => 'JPY', 'currency_name' => 'Japanese Yen'],
            ['name' => 'India', 'code' => 'IND', 'iso2' => 'IN', 'iso3' => 'IND', 'currency_code' => 'INR', 'currency_name' => 'Indian Rupee'],
            ['name' => 'Australia', 'code' => 'AUS', 'iso2' => 'AU', 'iso3' => 'AUS', 'currency_code' => 'AUD', 'currency_name' => 'Australian Dollar'],
            ['name' => 'Germany', 'code' => 'DEU', 'iso2' => 'DE', 'iso3' => 'DEU', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Netherlands', 'code' => 'NLD', 'iso2' => 'NL', 'iso3' => 'NLD', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Belgium', 'code' => 'BEL', 'iso2' => 'BE', 'iso3' => 'BEL', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'France', 'code' => 'FRA', 'iso2' => 'FR', 'iso3' => 'FRA', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Spain', 'code' => 'ESP', 'iso2' => 'ES', 'iso3' => 'ESP', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'Italy', 'code' => 'ITA', 'iso2' => 'IT', 'iso3' => 'ITA', 'currency_code' => 'EUR', 'currency_name' => 'Euro'],
            ['name' => 'United States', 'code' => 'USA', 'iso2' => 'US', 'iso3' => 'USA', 'currency_code' => 'USD', 'currency_name' => 'US Dollar'],
            ['name' => 'Canada', 'code' => 'CAN', 'iso2' => 'CA', 'iso3' => 'CAN', 'currency_code' => 'CAD', 'currency_name' => 'Canadian Dollar'],
            ['name' => 'Brazil', 'code' => 'BRA', 'iso2' => 'BR', 'iso3' => 'BRA', 'currency_code' => 'BRL', 'currency_name' => 'Brazilian Real'],
            ['name' => 'United Arab Emirates', 'code' => 'ARE', 'iso2' => 'AE', 'iso3' => 'ARE', 'currency_code' => 'AED', 'currency_name' => 'UAE Dirham'],
            ['name' => 'Saudi Arabia', 'code' => 'SAU', 'iso2' => 'SA', 'iso3' => 'SAU', 'currency_code' => 'SAR', 'currency_name' => 'Saudi Riyal'],
            ['name' => 'South Africa', 'code' => 'ZAF', 'iso2' => 'ZA', 'iso3' => 'ZAF', 'currency_code' => 'ZAR', 'currency_name' => 'South African Rand'],
            ['name' => 'Taiwan', 'code' => 'TWN', 'iso2' => 'TW', 'iso3' => 'TWN', 'currency_code' => 'TWD', 'currency_name' => 'New Taiwan Dollar'],
        ];

        foreach ($countries as $c) {
            Country::updateOrCreate(
                ['code' => $c['code']],
                $c
            );
        }
    }
}

