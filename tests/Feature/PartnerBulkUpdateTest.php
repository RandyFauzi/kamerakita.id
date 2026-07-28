<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerBulkUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_bulk_update_partners_successfully(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $partner1 = Partner::factory()->create([
            'group_name' => 'Group A',
            'status' => 'active',
            'base_hourly_rate' => 50000,
        ]);

        $partner2 = Partner::factory()->create([
            'group_name' => 'Group A',
            'status' => 'active',
            'base_hourly_rate' => 50000,
        ]);

        $postData = [
            'ids' => [$partner1->id, $partner2->id],
            'group_name' => 'Group B',
            'status' => 'suspended',
            'base_hourly_rate' => 75000,
        ];

        $response = $this->actingAs($admin)->post(route('partners.bulk-update'), $postData);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('partners', [
            'id' => $partner1->id,
            'group_name' => 'Group B',
            'status' => 'suspended',
            'base_hourly_rate' => 75000,
        ]);

        $this->assertDatabaseHas('partners', [
            'id' => $partner2->id,
            'group_name' => 'Group B',
            'status' => 'suspended',
            'base_hourly_rate' => 75000,
        ]);
    }
}
