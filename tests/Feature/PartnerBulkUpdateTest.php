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

        $response->assertRedirect()->assertSessionHasNoErrors();

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

    public function test_bulk_group_update_accepts_real_group_names_and_preserves_other_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $partners = Partner::factory()->count(2)->create([
            'group_name' => 'ASTRO',
            'status' => 'active',
            'base_hourly_rate' => 50000,
            'is_client_registered' => true,
        ]);
        $untouched = Partner::factory()->create(['group_name' => 'ASTRO']);

        $this->actingAs($admin)->post(route('partners.bulk-update'), [
            'ids' => $partners->modelKeys(),
            'group_name' => 'SPECTRE - (1618 Team)',
            'status' => 'no_change',
            'is_client_registered' => 'no_change',
            'base_hourly_rate' => '',
            'mitra_parent_id' => 'no_change',
        ])->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('success');

        foreach ($partners as $partner) {
            $this->assertDatabaseHas('partners', [
                'id' => $partner->id,
                'group_name' => 'SPECTRE - (1618 Team)',
                'status' => 'active',
                'base_hourly_rate' => 50000,
                'is_client_registered' => true,
            ]);
        }
        $this->assertSame('ASTRO', $untouched->fresh()->group_name);
    }

    public function test_bulk_group_sentinels_preserve_or_clear_the_group(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $partner = Partner::factory()->create(['group_name' => 'SPECTRE']);

        foreach (['no_change' => 'SPECTRE', 'clear' => null] as $option => $expected) {
            $this->actingAs($admin)->post(route('partners.bulk-update'), [
                'ids' => [$partner->id],
                'group_name' => $option,
                'base_hourly_rate' => 0,
            ])->assertSessionHasNoErrors();

            $this->assertSame($expected, $partner->fresh()->group_name);
            $this->assertEquals(0, $partner->fresh()->base_hourly_rate);
        }
    }

    public function test_invalid_bulk_group_does_not_partially_update_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $partner = Partner::factory()->create(['group_name' => 'ASTRO', 'base_hourly_rate' => 50000]);

        $this->actingAs($admin)->post(route('partners.bulk-update'), [
            'ids' => [$partner->id],
            'group_name' => str_repeat('a', 51),
            'base_hourly_rate' => 75000,
        ])->assertSessionHasErrors('group_name');

        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'group_name' => 'ASTRO',
            'base_hourly_rate' => 50000,
        ]);
    }

    public function test_bulk_update_rejects_empty_or_duplicate_account_selections(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $partner = Partner::factory()->create(['group_name' => 'ASTRO']);

        foreach ([[], [$partner->id, $partner->id]] as $ids) {
            $response = $this->actingAs($admin)->post(route('partners.bulk-update'), [
                'ids' => $ids,
                'group_name' => 'SPECTRE',
            ]);

            $response->assertSessionHasErrors($ids === [] ? 'ids' : 'ids.0');
            $this->assertSame('ASTRO', $partner->fresh()->group_name);
        }
    }
}
