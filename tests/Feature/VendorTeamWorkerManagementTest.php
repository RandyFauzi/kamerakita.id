<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorTeamWorkerManagementTest extends TestCase
{
    use RefreshDatabase;

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
}
