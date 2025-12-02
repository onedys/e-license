<?php

namespace Database\Seeders;

use App\Models\LicensePool;
use App\Models\Product;
use Illuminate\Database\Seeder;

class LicensePoolSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        
        $sampleKeys = [
            'XXXXX-XXXXX-XXXXX-XXXXX-XXXXX',
            'YYYYY-YYYYY-YYYYY-YYYYY-YYYYY',
            'ZZZZZ-ZZZZZ-ZZZZZ-ZZZZZ-ZZZZZ',
        ];
        
        foreach ($products as $product) {
            foreach ($sampleKeys as $key) {
                LicensePool::create([
                    'product_id' => $product->id,
                    'license_key' => $key,
                    'keyname_with_dash' => $key,
                    'errorcode' => '0xC004C008',
                    'product_name' => $product->name,
                    'is_retail' => true,
                    'status' => 'active',
                    'validated_at' => now(),
                    'last_validated_at' => now(),
                ]);
            }
        }
    }
}