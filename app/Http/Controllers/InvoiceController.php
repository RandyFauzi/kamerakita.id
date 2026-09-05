<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    /**
     * Show the invoice generator and history tabs.
     */
    public function index()
    {
        $invoices = Invoice::orderBy('created_at', 'desc')->get();
        return view('invoices.index', compact('invoices'));
    }

    /**
     * Store a new invoice in the database and redirect to preview.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'total_workers' => 'required|numeric',
            'total_approved_hours' => 'required|numeric',
            'invoice_no' => 'nullable|string',
            'invoice_date' => 'nullable|date',
        ]);

        $invoiceNo = $validated['invoice_no'] ?? 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $invoiceDate = $validated['invoice_date'] ?? date('Y-m-d');
        
        $totalAmount = floatval($validated['total_approved_hours']) * 3.5;

        $invoice = Invoice::create([
            'invoice_no' => $invoiceNo,
            'invoice_date' => $invoiceDate,
            'total_workers' => $validated['total_workers'],
            'total_approved_hours' => $validated['total_approved_hours'],
            'total_amount' => $totalAmount,
        ]);

        return redirect()->route('invoices.show', $invoice->id);
    }

    /**
     * Show the preview for the printable invoice.
     */
    public function show($id)
    {
        $invoice = Invoice::findOrFail($id);

        $data = [
            'invoice_no' => $invoice->invoice_no,
            'invoice_date' => date('F j, Y', strtotime($invoice->invoice_date)),
            'total_workers' => $invoice->total_workers,
            'total_approved_hours' => number_format((float)$invoice->total_approved_hours, 2, '.', ''),
            'total_amount' => number_format((float)$invoice->total_amount, 2, '.', ''),
        ];

        return view('invoices.preview', $data);
    }

    /**
     * Delete an invoice.
     */
    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice berhasil dihapus.');
    }
}
