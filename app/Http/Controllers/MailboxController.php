<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MailboxController extends Controller
{
    public function index()
    {
        $emails = Auth::user()->capturedEmails()->orderBy('received_at', 'desc')->get();
        return view('mailbox.index', compact('emails'));
    }
}
