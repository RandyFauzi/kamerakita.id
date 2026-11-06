<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorTeamWorkerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_creation_uses_numeric_id_and_retries_a_collision_without_orphan_users(): void
    {
        $mitraUser = User::factory()->create();
        $mitra = Partner::factory()->create([
            'user_id' => $mitraUser->id,
            'partner_role' => 'mitra',
            'mitra_id' => 'KMK-999',
        ]);
        Partner::factory()->create(['mitra_id' => 'KMK-1000']);
        Partner::factory()->create(['mitra_id' => 'OTHER-99999']);
        $userCount = User::count();
        $attempts = 0;
        $dispatcher = clone Partner::getEventDispatcher();
        Partner::creating(function (Partner $worker) use (&$attempts) {
            if ($worker->email === 'newworker@kamerakitaid.site' && ++$attempts === 1) {
                // Simulate another request taking our allocated ID before the insert.
                $worker->mitra_id = 'KMK-1000';
            }
        });

        try {
            $response = $this->actingAs($mitraUser)->post(route('vendor.workers.store'), [
                'name' => 'New Worker',
                'username' => 'newworker',
                'whatsapp_number' => '08123456789',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);
        } finally {
            Partner::setEventDispatcher($dispatcher);
        }

        $response->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertSame(2, $attempts);
        $this->assertSame($userCount + 1, User::count());
        $this->assertDatabaseHas('partners', [
            'email' => 'newworker@kamerakitaid.site',
            'mitra_id' => 'KMK-1001',
            'mitra_parent_id' => $mitra->id,
        ]);
    }

    public function test_next_mitra_id_handles_empty_table_and_five_digit_numbers(): void
    {
        $this->assertSame('KMK-001', Partner::nextMitraId());
        Partner::factory()->create(['mitra_id' => 'KMK-9999']);
        Partner::factory()->create(['mitra_id' => 'KMK-10000']);
        Partner::factory()->create(['mitra_id' => 'KMK-99999-invalid']);

        $this->assertSame('KMK-10001', Partner::nextMitraId());
    }

    public function test_mitra_can_access_workers_management_page_and_see_team()
    {
        $mitraUser = User::factory()->create(['role' => 'user']);
        $mitra = Partner::factory()->create([
            'user_id' => $mitraUser->id,
            'partner_role' => 'mitra',
            'full_name' => 'Mitra Utama',
            'mitra_id' => 'KMK-001',
        ]);

        $workerUser1 = User::factory()->create(['email' => 'worker1@kamerakitaid.site']);
        $worker1 = Partner::factory()->create([
            'user_id' => $workerUser1->id,
            'partner_role' => 'worker',
            'mitra_parent_id' => $mitra->id,
            'full_name' => 'Worker Satu',
            'mitra_id' => 'KMK-101',
            'whatsapp_number' => '081234567890',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_owner' => 'Worker Satu',
        ]);

        $workerUser2 = User::factory()->create(['email' => 'worker2@kamerakitaid.site']);
        $worker2 = Partner::factory()->create([
            'user_id' => $workerUser2->id,
            'partner_role' => 'worker',
            'mitra_parent_id' => $mitra->id,
            'full_name' => 'Worker Dua',
            'mitra_id' => 'KMK-102',
            'whatsapp_number' => '089876543210',
        ]);

        $this->actingAs($mitraUser);

        $response = $this->get(route('vendor.workers.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Anggota Tim');
        $response->assertSee('Salin Kontak');
        $response->assertSee('Salin Email');
        $response->assertSee('Salin No WA');
        $response->assertSee('Salin No Rek');
        $response->assertSee('Salin Nama');
        $response->assertSee('Worker Satu');
        $response->assertSee('Worker Dua');
        $response->assertSee('KMK-101');
        $response->assertSee('KMK-102');
    }

    public function test_mitra_can_update_worker_details()
    {
        $mitraUser = User::factory()->create(['role' => 'user']);
        $mitra = Partner::factory()->create([
            'user_id' => $mitraUser->id,
            'partner_role' => 'mitra',
            'full_name' => 'Mitra Boss',
        ]);

        $workerUser = User::factory()->create(['name' => 'Worker Lama', 'email' => 'workerlama@kamerakitaid.site']);
        $worker = Partner::factory()->create([
            'user_id' => $workerUser->id,
            'partner_role' => 'worker',
            'mitra_parent_id' => $mitra->id,
            'full_name' => 'Worker Lama',
            'whatsapp_number' => '081111111111',
        ]);

        $this->actingAs($mitraUser);

        $response = $this->put(route('vendor.workers.update', $worker->id), [
            'name' => 'Worker Baru Diperbarui',
            'whatsapp_number' => '082222222222',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '987654321',
            'bank_account_owner' => 'Worker Baru Diperbarui',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('partners', [
            'id' => $worker->id,
            'full_name' => 'Worker Baru Diperbarui',
            'whatsapp_number' => '082222222222',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '987654321',
            'bank_account_owner' => 'Worker Baru Diperbarui',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $workerUser->id,
            'name' => 'Worker Baru Diperbarui',
        ]);
    }

    public function test_non_mitra_cannot_access_workers_page()
    {
        $workerUser = User::factory()->create(['role' => 'user']);
        Partner::factory()->create([
            'user_id' => $workerUser->id,
            'partner_role' => 'worker',
        ]);

        $this->actingAs($workerUser);

        $response = $this->get(route('vendor.workers.index'));
        $response->assertStatus(403);
    }

    public function test_vendor_workers_page_displays_registration_date(): void
    {
        $mitraUser = User::factory()->create(['role' => 'mitra']);
        $mitra = Partner::factory()->create([
            'user_id' => $mitraUser->id,
            'partner_role' => 'mitra',
            'mitra_id' => 'KMK-001',
        ]);

        $workerUser = User::factory()->create();
        $worker = Partner::factory()->create([
            'user_id' => $workerUser->id,
            'partner_role' => 'worker',
            'mitra_parent_id' => $mitra->id,
            'mitra_id' => 'KMK-002',
            'full_name' => 'Worker Uji Tanggal',
            'registration_date' => '2026-10-15',
        ]);

        $response = $this->actingAs($mitraUser)->get(route('vendor.workers.index'));
        $response->assertStatus(200);
        $response->assertSee('15 Okt 2026');
        $response->assertSee('registered_at');
        $response->assertSee('Pendaftaran Terbaru');
    }
}
