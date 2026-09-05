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
        if (app()->environment('production')) {
            $this->command->error('WARNING: Seeder cannot be run in production environment.');
            return;
        }

        $defaultPassword = env('SEEDER_DEFAULT_PASSWORD') ? bcrypt(env('SEEDER_DEFAULT_PASSWORD')) : bcrypt(\Illuminate\Support\Str::random(16));

        // 1. Seed System Users (Roles)
        User::factory()->create([
            'name' => 'Demo Super Admin',
            'email' => 'superadmin@example.com',
            'password' => $defaultPassword,
            'role' => 'superadmin',
        ]);

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin2@example.com',
            'password' => $defaultPassword,
            'role' => 'superadmin',
        ]);

        User::factory()->create([
            'name' => 'Tim Admin / QC',
            'email' => 'admin@example.com',
            'password' => $defaultPassword,
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Tim Keuangan',
            'email' => 'finance@example.com',
            'password' => $defaultPassword,
            'role' => 'finance',
        ]);

        // 2. Seed 5 Mitra (Managers/Coordinators)
        $mitraList = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = User::factory()->create([
                'name' => 'Mitra ' . $i,
                'email' => 'mitra' . $i . '@example.com',
                'password' => $defaultPassword,
                'role' => 'worker', // Can log in and view things
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
                'name' => 'Worker Mitra ' . $i,
                'email' => "worker{$i}@example.com",
                'password' => $defaultPassword,
                'role' => 'worker', // Set default role
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
