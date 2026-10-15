<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index()
    {
        $data = config('portfolio');
        $data['approval_percentage'] = round($data['atlas']['approved_minutes'] / $data['atlas']['recorded_minutes'] * 100, 1);

        return view('portofolio', ['portfolio' => $data]);
    }

    public function inquiry(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'company' => 'required|string|max:150',
            'email' => 'required|email|max:254',
            'brief' => 'required|string|min:10|max:2000',
        ]);
        $message = __('portfolio.form.message')."\n\n";
        foreach ($data as $field => $value) {
            $message .= __('portfolio.form.'.$field).': '.$value."\n";
        }

        // The visitor reviews and sends the brief in WhatsApp. No contact record
        // is persisted and this endpoint does not send messages automatically.
        $number = preg_replace('/\D/', '', config('portfolio.contact_whatsapp'));

        return redirect()->away('https://wa.me/'.$number.'?text='.rawurlencode($message));
    }
}
