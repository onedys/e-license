<?php

namespace Database\Factories;

use App\Models\LicensePool;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicensePoolFactory extends Factory
{
    protected $model = LicensePool::class;

    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        
        // Generate a fake license key in the correct format
        $licenseKey = implode('-', [
            strtoupper($this->faker->bothify('?????')),
            strtoupper($this->faker->bothify('?????')),
            strtoupper($this->faker->bothify('?????')),
            strtoupper($this->faker->bothify('?????')),
            strtoupper($this->faker->bothify('?????')),
        ]);
        
        return [
            'product_id' => $product->id,
            'license_key' => $licenseKey,
            'keyname_with_dash' => $licenseKey,
            'errorcode' => $this->faker->randomElement(['0xC004C008', 'Online Key']),
            'product_name' => $product->name,
            'is_retail' => $this->faker->boolean(80),
            'remaining' => $this->faker->numberBetween(-1, 10),
            'blocked' => $this->faker->randomElement([-1, 0, 1]),
            'status' => $this->faker->randomElement(['active', 'blocked', 'invalid']),
            'validated_at' => $this->faker->dateTimeThisYear(),
            'last_validated_at' => $this->faker->dateTimeThisMonth(),
            'validation_count' => $this->faker->numberBetween(0, 10),
        ];
    }
    
    public function active(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
                'errorcode' => '0xC004C008',
                'blocked' => -1,
            ];
        });
    }
    
    public function blocked(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'blocked',
                'errorcode' => '0xC004C060',
                'blocked' => 1,
            ];
        });
    }
    
    public function invalid(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'invalid',
                'errorcode' => 'Invalid',
                'blocked' => 1,
            ];
        });
    }
}