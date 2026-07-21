<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\Country;
use App\Models\Port;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'company_name' => 'Samsung Electronics Co., Ltd.',
                'country_code' => 'KOR', // South Korea
                'port_name'    => 'Busan',
                'contact_person' => 'Kim Jin-woo',
                'email'        => 'supplier.samsung@samsung-partner.com',
                'phone'        => '+82-2-2053-3000',
                'address'      => '129, Samsung-ro, Yeongtong-gu, Suwon-si, Gyeonggi-do, Korea',
                'supplier_type'=> 'Manufacturer',
                'risk_level'   => 'Low',
                'rating'       => 4.9,
            ],
            [
                'company_name' => 'Foxconn Technology Group',
                'country_code' => 'CHN', // China
                'port_name'    => 'Shanghai',
                'contact_person' => 'Terry Gou',
                'email'        => 'foxconn.import@foxconn.com.cn',
                'phone'        => '+86-755-2812-9588',
                'address'      => 'No. 2, 2nd Donghuan Road, Longhua Street, Baoan District, Shenzhen, Guangdong, China',
                'supplier_type'=> 'Manufacturer',
                'risk_level'   => 'Medium',
                'rating'       => 4.2,
            ],
            [
                'company_name' => 'TSMC Limited',
                'country_code' => 'TWN', // Taiwan
                'port_name'    => 'Kaohsiung',
                'contact_person' => 'C.C. Wei',
                'email'        => 'contact@tsmc.com.tw',
                'phone'        => '+886-3-5636688',
                'address'      => '8, Li-Hsin Rd. 6, Hsinchu Science Park, Hsinchu 300-78, Taiwan',
                'supplier_type'=> 'Manufacturer',
                'risk_level'   => 'Medium',
                'rating'       => 4.8,
            ],
            [
                'company_name' => 'ABC Electronics Corp',
                'country_code' => 'CHN',
                'port_name'    => 'Shanghai',
                'contact_person' => 'Li Wei',
                'email'        => 'sales@abc-electronics.cn',
                'phone'        => '+86-21-61234567',
                'address'      => 'Building 4, Pudong New Area, Shanghai, China',
                'supplier_type'=> 'Manufacturer',
                'risk_level'   => 'High',
                'rating'       => 3.5,
            ],
            [
                'company_name' => 'Tokyo Electron Ltd.',
                'country_code' => 'JPN',
                'port_name'    => 'Tokyo',
                'contact_person' => 'Toshiki Kawai',
                'email'        => 'sales@tel.co.jp',
                'phone'        => '+81-3-5561-7000',
                'address'      => '3-1 Akasaka 5-chome, Minato-ku, Tokyo, Japan',
                'supplier_type'=> 'Distributor',
                'risk_level'   => 'Low',
                'rating'       => 4.7,
            ],
            [
                'company_name' => 'DHL Global Forwarding',
                'country_code' => 'DEU',
                'port_name'    => 'Rotterdam',
                'contact_person' => 'Frank Appel',
                'email'        => 'logistics.support@dhl.com',
                'phone'        => '+49-228-182-0',
                'address'      => 'Charles-de-Gaulle-Str. 20, 53113 Bonn, Germany',
                'supplier_type'=> 'Trading Company',
                'risk_level'   => 'Low',
                'rating'       => 4.5,
            ]
        ];

        foreach ($data as $item) {
            $country = Country::where('iso3', $item['country_code'])
                ->orWhere('name', 'like', '%' . $item['country_code'] . '%')
                ->first();

            if (!$country) {
                // fallback
                $country = Country::first() ?? Country::create(['name' => 'Global', 'code' => 'GLB', 'iso3' => 'GLB']);
            }

            // Find port or get first port of country or any port
            $port = Port::where('name', 'like', '%' . $item['port_name'] . '%')->first()
                 ?? Port::where('country_id', $country->id)->first()
                 ?? Port::first();

            Supplier::updateOrCreate(
                ['company_name' => $item['company_name']],
                [
                    'country_id'    => $country->id,
                    'port_id'       => $port?->id,
                    'contact_person'=> $item['contact_person'],
                    'email'         => $item['email'],
                    'phone'         => $item['phone'],
                    'address'       => $item['address'],
                    'supplier_type' => $item['supplier_type'],
                    'risk_level'    => $item['risk_level'],
                    'rating'        => $item['rating'],
                    'status'        => 'Active',
                ]
            );
        }
    }
}