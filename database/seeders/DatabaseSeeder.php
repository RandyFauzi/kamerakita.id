<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed System Users (Roles)
        User::factory()->create([
            'name' => 'Randy Fauzi (Admin)',
            'email' => 'randyfauzi24@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
        ]);

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@kamerakita.id',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
        ]);

        User::factory()->create([
            'name' => 'Tim Verifikator',
            'email' => 'verifikator@kamerakita.id',
            'password' => bcrypt('password'),
            'role' => 'verifikator',
        ]);

        User::factory()->create([
            'name' => 'Tim Keuangan',
            'email' => 'finance@kamerakita.id',
            'password' => bcrypt('password'),
            'role' => 'finance',
        ]);

        // 2. Seed 5 Mitra (Managers/Coordinators)
        $mitraList = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = User::factory()->create([
                'name' => "Mitra Coordinator {$i}",
                'email' => "contributor{$i}@kamerakita.id",
                'password' => bcrypt('password'),
                'role' => 'verifikator', // Can log in and view things
            ]);

            $mitraList[] = Partner::factory()->create([
                'partner_role' => 'mitra',
                'mitra_parent_id' => null,
                'full_name' => "Mitra Coordinator {$i}",
                'user_id' => $user->id,
            ]);
        }

        // 3. Seed 95 Workers assigned to Mitra
        for ($i = 1; $i <= 95; $i++) {
            $user = User::factory()->create([
                'name' => "Worker Mitra {$i}",
                'email' => "worker{$i}@kamerakita.id",
                'password' => bcrypt('password'),
                'role' => 'verifikator', // Set default role
            ]);

            $randomMitra = $mitraList[array_rand($mitraList)];

            $worker = Partner::factory()->create([
                'partner_role' => 'worker',
                'mitra_parent_id' => $randomMitra->id,
                'full_name' => "Worker Mitra {$i}",
                'user_id' => $user->id,
            ]);

            // Seed 3 to 8 video submissions for each worker
            $numVideos = rand(3, 8);
            VideoWorkReport::factory($numVideos)->create([
                'partner_id' => $worker->id,
            ]);
        }

        // 4. Seed Mock Client Invoices for Mytronlabs
        \App\Models\ClientInvoice::create([
            'invoice_month' => 'Mei 2026',
            'total_minutes_billed' => 12000,
            'total_amount_usd' => 1000.00,
            'status' => 'paid_by_client',
        ]);

        \App\Models\ClientInvoice::create([
            'invoice_month' => 'Juni 2026',
            'total_minutes_billed' => 15000,
            'total_amount_usd' => 1250.00,
            'status' => 'unpaid_by_client',
        ]);
    }
}
