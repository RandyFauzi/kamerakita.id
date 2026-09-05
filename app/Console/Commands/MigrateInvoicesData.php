<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\ClientInvoice;
use App\Models\InvoiceItem;
use Carbon\Carbon;

class MigrateInvoicesData extends Command
{
    protected $signature = 'kamerakita:migrate-invoices';
    protected $description = 'Safely migrate ClientInvoice data into the new unified Invoice table';

    public function handle()
    {
        $this->info('Starting invoice data migration...');

        // 1. Ensure dummy client exists
        $client = Client::firstOrCreate(
            ['name' => 'MyTron Labs'],
            [
                'email' => 'billing@mytronlabs.com',
                'address' => '123 Tech Blvd, SF',
                'default_currency' => 'USD',
                'default_rate' => 3.50,
            ]
        );

        $this->info("Client {$client->name} ready (ID: {$client->id}).");

        // 2. Migrate existing Invoice records
        $oldInvoices = Invoice::whereNull('client_id')->get();
        foreach ($oldInvoices as $inv) {
            $inv->update([
                'client_id' => $client->id,
                'client_name' => $client->name,
                'client_email' => $client->email,
                'unit_rate' => $client->default_rate,
                'currency' => $client->default_currency,
                'source_approved_hours' => $inv->total_approved_hours,
                'billable_hours' => $inv->total_approved_hours,
                'status' => 'ISSUED', // Legacy is assumed issued
            ]);

            // Create item if not exists
            if ($inv->items()->count() === 0) {
                $inv->items()->create([
                    'description' => 'Video Data Collection (Legacy)',
                    'quantity' => $inv->total_approved_hours,
                    'unit' => 'hours',
                    'unit_rate' => $client->default_rate,
                    'amount' => $inv->total_amount,
                ]);
            }
        }
        $this->info("Migrated {$oldInvoices->count()} existing Invoices.");

        // 3. Migrate ClientInvoices
        $clientInvoices = ClientInvoice::all();
        $migratedCount = 0;
        
        foreach ($clientInvoices as $ci) {
            // Check if already migrated
            $existing = Invoice::where('adjustment_reason', 'LIKE', "%Migrated from ClientInvoice {$ci->id}%")->first();
            if ($existing) continue;

            // Generate unique no
            do {
                $invoiceNo = 'INV-LG-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            } while (Invoice::where('invoice_no', $invoiceNo)->exists());

            $hours = round($ci->total_minutes_billed / 60, 2);

            $newInv = Invoice::create([
                'invoice_no' => $invoiceNo,
                'invoice_date' => $ci->created_at->toDateString(),
                'total_workers' => 0,
                'total_approved_hours' => $hours,
                'total_amount' => $ci->total_amount_usd,
                'client_id' => $client->id,
                'client_name' => $client->name,
                'unit_rate' => $client->default_rate,
                'currency' => 'USD',
                'source_approved_hours' => $hours,
                'billable_hours' => $hours,
                'status' => 'ISSUED', // assume issued
                'adjustment_reason' => "Migrated from ClientInvoice {$ci->id} (Month: {$ci->invoice_month})",
                'created_at' => $ci->created_at,
                'updated_at' => $ci->updated_at,
            ]);

            $newInv->items()->create([
                'description' => "Billing for {$ci->invoice_month}",
                'quantity' => $hours,
                'unit' => 'hours',
                'unit_rate' => $client->default_rate,
                'amount' => $ci->total_amount_usd,
            ]);
            
            $migratedCount++;
        }

        $this->info("Migrated {$migratedCount} ClientInvoice records into Invoices.");
        $this->info('Migration complete!');
    }
}
