<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerFactory extends Factory
{
    protected $model = Partner::class;
    protected static $counter = 1;

    public function definition(): array
    {
        $mitraId = 'KMK-' . str_pad(self::$counter++, 3, '0', STR_PAD_LEFT);
        $role = $this->faker->randomElement(['worker', 'worker', 'worker', 'mitra']);
        
        return [
            'mitra_id' => $mitraId,
            'partner_role' => $role,
            'mitra_parent_id' => null, // will be linked in Seeder
            'full_name' => $fullName = $this->faker->name(),
            'whatsapp_number' => '08' . $this->faker->numerify('##########'),
            'has_headstrap' => $this->faker->boolean(35),
            'status' => $this->faker->randomElement(['active', 'active', 'active', 'suspended']),
            'base_hourly_rate' => 54000,
            'user_id' => User::factory(),
        ];
    }
}
