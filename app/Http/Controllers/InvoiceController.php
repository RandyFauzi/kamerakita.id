<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('client')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where('invoice_no', 'like', '%' . $request->search . '%')
                  ->orWhere('client_name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->paginate(25);
        $clients = Client::all();

        return view('invoices.index', compact('invoices', 'clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'client_name' => 'nullable|string|max:255',
            'client_address' => 'nullable|string',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'invoice_date' => 'required|date',
            'billable_hours' => 'required|numeric|min:0',
            'unit_rate' => 'required|numeric|min:0',
        ]);

        $client = Client::findOrFail($validated['client_id']);
        $amount = $validated['billable_hours'] * $validated['unit_rate'];

        // Use custom inputs if provided, otherwise fallback to template
        $finalClientName = !empty($validated['client_name']) ? $validated['client_name'] : $client->name;
        $finalClientAddress = !empty($validated['client_address']) ? $validated['client_address'] : $client->address;

        DB::beginTransaction();
        try {
            $year = date('Y', strtotime($validated['invoice_date']));
            $latest = Invoice::where('invoice_no', 'like', "INV-{$year}-%")->orderBy('id', 'desc')->first();
            $nextSeq = 1;
            if ($latest) {
                $parts = explode('-', $latest->invoice_no);
                $nextSeq = intval(end($parts)) + 1;
            }
            $invoiceNo = "INV-{$year}-" . str_pad($nextSeq, 6, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'invoice_no' => $invoiceNo,
                'invoice_date' => $validated['invoice_date'],
                'client_id' => $client->id,
                'client_name' => $finalClientName,
                'client_email' => $client->email,
                'client_address' => $finalClientAddress,
                'client_tax_id' => $client->tax_id,
                'unit_rate' => $validated['unit_rate'],
                'currency' => $client->default_currency,
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
                'source_approved_hours' => $validated['billable_hours'],
                'billable_hours' => $validated['billable_hours'],
                'adjustment_hours' => 0,
                'adjustment_reason' => null,
                'total_approved_hours' => $validated['billable_hours'],
                'total_workers' => 0,
                'total_amount' => $amount,
                'status' => 'DRAFT'
            ]);

            $invoice->items()->create([
                'description' => 'Video Data Collection',
                'quantity' => $validated['billable_hours'],
                'unit' => 'hours',
                'unit_rate' => $validated['unit_rate'],
                'amount' => $amount
            ]);

            DB::commit();
            return redirect()->route('invoices.show', $invoice->id)->with('success', 'Draft created.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to generate invoice: ' . $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        $invoice = Invoice::with('items')->findOrFail($id);
        return view('invoices.preview', compact('invoice'));
    }

    public function issue($id)
    {
        $invoice = Invoice::findOrFail($id);
        if ($invoice->status !== 'DRAFT') abort(403, 'Only draft can be issued.');
        $invoice->issue();
        return back()->with('success', 'Invoice Issued!');
    }

    public function send($id)
    {
        $invoice = Invoice::findOrFail($id);
        if ($invoice->status !== 'ISSUED') abort(403, 'Must be issued first.');
        $invoice->markAsSent();
        return back()->with('success', 'Invoice marked as sent.');
    }

    public function pay($id)
    {
        $invoice = Invoice::findOrFail($id);
        if ($invoice->status !== 'SENT' && $invoice->status !== 'ISSUED') abort(403, 'Cannot pay.');
        $invoice->markAsPaid();
        return back()->with('success', 'Invoice paid.');
    }

    public function voidInvoice(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        if (in_array($invoice->status, ['DRAFT', 'VOID'])) abort(403, 'Cannot void this state.');
        
        $request->validate(['reason' => 'required|string']);
        $invoice->voidInvoice($request->reason, auth()->user()->name ?? 'System');
        
        return back()->with('success', 'Invoice voided.');
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        if ($invoice->status !== 'DRAFT') {
            return back()->with('error', 'Cannot delete an issued invoice. Use Void instead.');
        }
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Draft deleted.');
    }
}
