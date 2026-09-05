<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RateCard;

class RateCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rates = [
            ['project' => 'atlas', 'partner_role' => 'worker', 'rate_per_hour' => 50000],
            ['project' => 'atlas', 'partner_role' => 'mitra', 'rate_per_hour' => 63000],
            ['project' => 'atlas', 'partner_role' => 'commission', 'rate_per_hour' => 9000],
            ['project' => 'atlas', 'partner_role' => 'vendor', 'rate_per_hour' => 65000],
        ];

        foreach ($rates as $rate) {
            RateCard::firstOrCreate(
                [
                    'project' => $rate['project'],
                    'partner_role' => $rate['partner_role'],
                    'effective_from' => '2026-08-01',
                ],
                [
                    'rate_per_hour' => $rate['rate_per_hour'],
                    'effective_until' => null,
                ]
            );
        }
    }
}
