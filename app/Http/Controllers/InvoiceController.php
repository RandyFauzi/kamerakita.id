<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Show the form to create an invoice.
     */
    public function create()
    {
        return view('invoices.create');
    }

    /**
     * Generate the preview for the printable invoice.
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'total_workers' => 'required|numeric',
            'total_approved_hours' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'invoice_no' => 'nullable|string',
            'invoice_date' => 'nullable|date',
        ]);

        $invoiceNo = $validated['invoice_no'] ?? 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $invoiceDate = $validated['invoice_date'] ?? date('Y-m-d');
        
        $data = [
            'invoice_no' => $invoiceNo,
            'invoice_date' => date('F j, Y', strtotime($invoiceDate)),
            'total_workers' => $validated['total_workers'],
            'total_approved_hours' => number_format((float)$validated['total_approved_hours'], 2, '.', ''),
            'total_amount' => number_format((float)$validated['total_amount'], 2, '.', ''),
        ];

        return view('invoices.preview', $data);
    }
}
