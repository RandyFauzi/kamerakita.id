<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerListPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_all_keeps_filters_and_ignores_previous_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Partner::factory()->count(16)->create(['full_name' => 'Matching worker', 'partner_role' => 'worker']);
        Partner::factory()->create(['full_name' => 'Excluded person', 'partner_role' => 'mitra']);

        $this->actingAs($admin)->get(route('partners.index', ['search' => 'Matching']))
            ->assertOk()->assertViewHas('partners', fn ($rows) => $rows->count() === 15 && $rows->total() === 16);

        $this->get(route('partners.index', ['search' => 'Matching', 'per_page' => 'all', 'page' => 2]))
            ->assertOk()->assertViewHas('partners', fn ($rows) => $rows->count() === 16 && !$rows->hasPages()
                && $rows->currentPage() === 1 && $rows->every(fn ($row) => $row->full_name === 'Matching worker'));

        $this->get(route('partners.index', ['search' => 'No match', 'per_page' => 'all']))
            ->assertOk()->assertViewHas('partners', fn ($rows) => $rows->isEmpty());
    }

    public function test_search_by_bank_account_number(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $partnerA = Partner::factory()->create([
            'full_name' => 'Budi Santoso',
            'bank_account_number' => '5420198821',
        ]);
        $partnerB = Partner::factory()->create([
            'full_name' => 'Siti Nurhaliza',
            'bank_account_number' => '1234567890',
        ]);

        // Search exact bank account number
        $this->actingAs($admin)->get(route('partners.index', ['search' => '5420198821']))
            ->assertOk()
            ->assertViewHas('partners', fn ($rows) => $rows->count() === 1 && $rows->first()->id === $partnerA->id);

        // Search with formatting (dashes/spaces)
        $this->get(route('partners.index', ['search' => '5420-1988-21']))
            ->assertOk()
            ->assertViewHas('partners', fn ($rows) => $rows->count() === 1 && $rows->first()->id === $partnerA->id);

        // Export contacts search by bank account
        $this->getJson(route('partners.export-contacts', ['search' => '5420198821']))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['name' => 'Budi Santoso']);
    }
}
