<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Windows 10 Pro Retail',
                'slug' => 'windows-10-pro-retail',
                'description' => 'Lisensi Windows 10 Professional Retail untuk aktivasi permanen.',
                'price' => 250000,
                'category' => 'windows',
                'features' => ['Retail', 'Permanent', 'Digital Delivery'],
                'stock_type' => 1,
            ],
            [
                'name' => 'Windows 11 Pro Retail',
                'slug' => 'windows-11-pro-retail',
                'description' => 'Lisensi Windows 11 Professional Retail versi terbaru.',
                'price' => 350000,
                'category' => 'windows',
                'features' => ['Retail', 'Permanent', 'Latest Version'],
                'stock_type' => 1,
            ],
            [
                'name' => 'Microsoft Office 2021 Professional Plus',
                'slug' => 'office-2021-pro-plus',
                'description' => 'Paket lengkap Microsoft Office 2021 Professional Plus.',
                'price' => 450000,
                'category' => 'office',
                'features' => ['Full Package', 'Lifetime', 'Retail Key'],
                'stock_type' => 1,
            ],
            [
                'name' => 'Microsoft Office 2019 Professional Plus',
                'slug' => 'office-2019-pro-plus',
                'description' => 'Microsoft Office 2019 Professional Plus retail license.',
                'price' => 300000,
                'category' => 'office',
                'features' => ['Full Package', 'Retail', 'Digital'],
                'stock_type' => 1,
            ],
            [
                'name' => 'Windows Server 2022 Standard',
                'slug' => 'windows-server-2022-standard',
                'description' => 'Lisensi Windows Server 2022 Standard untuk server.',
                'price' => 1500000,
                'category' => 'server',
                'features' => ['Server OS', 'Standard Edition', 'Retail'],
                'stock_type' => 2,
                'available_stock' => 10,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}