<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Invoice;

class InvoiceLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->client = Client::create([
            'name' => 'Test Client',
            'email' => 'test@client.com',
            'default_rate' => 10.00,
            'default_currency' => 'USD'
        ]);
    }

    public function test_admin_can_create_invoice_draft_with_adjustment()
    {
        $response = $this->actingAs($this->admin)->post(route('invoices.store'), [
            'client_id' => $this->client->id,
            'period_start' => '2026-08-01',
            'period_end' => '2026-08-31',
            'invoice_date' => '2026-09-01',
            'billable_hours' => 90
        ]);

        $this->assertDatabaseHas('invoices', [
            'client_id' => $this->client->id,
            'status' => 'DRAFT',
            'source_approved_hours' => 90,
            'billable_hours' => 90,
            'adjustment_hours' => 0,
            'total_amount' => 900 // 90 * 10.00
        ]);
        
        $invoice = Invoice::first();
        $response->assertRedirect(route('invoices.show', $invoice->id));
    }

    public function test_invoice_lifecycle_transitions()
    {
        $invoice = Invoice::create([
            'invoice_no' => 'INV-TEST-01',
            'invoice_date' => '2026-09-01',
            'client_id' => $this->client->id,
            'source_approved_hours' => 10,
            'billable_hours' => 10,
            'total_approved_hours' => 10,
            'total_amount' => 100,
            'total_workers' => 0,
            'status' => 'DRAFT'
        ]);

        // Issue
        $this->actingAs($this->admin)->post(route('invoices.issue', $invoice->id));
        $this->assertEquals('ISSUED', $invoice->fresh()->status);

        // Delete should fail
        $this->actingAs($this->admin)->delete(route('invoices.destroy', $invoice->id));
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]); // Still exists

        // Mark Sent
        $this->actingAs($this->admin)->post(route('invoices.send', $invoice->id));
        $this->assertEquals('SENT', $invoice->fresh()->status);

        // Mark Paid
        $this->actingAs($this->admin)->post(route('invoices.pay', $invoice->id));
        $this->assertEquals('PAID', $invoice->fresh()->status);
    }

    public function test_voiding_requires_reason()
    {
        $invoice = Invoice::create([
            'invoice_no' => 'INV-TEST-02',
            'invoice_date' => '2026-09-01',
            'client_id' => $this->client->id,
            'source_approved_hours' => 10,
            'billable_hours' => 10,
            'total_approved_hours' => 10,
            'total_amount' => 100,
            'total_workers' => 0,
            'status' => 'ISSUED'
        ]);

        $response = $this->actingAs($this->admin)->post(route('invoices.void', $invoice->id), [
            'reason' => 'Client cancelled project'
        ]);

        $invoice->refresh();
        $this->assertEquals('VOID', $invoice->status);
        $this->assertEquals('Client cancelled project', $invoice->void_reason);
    }
}
