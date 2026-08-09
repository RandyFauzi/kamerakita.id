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

    public function toggleRead(Request $request, \App\Models\CapturedEmail $email)
    {
        if ($email->user_id !== Auth::id()) abort(403);
        $email->update(['is_read' => $request->is_read]);
        return response()->json(['success' => true]);
    }

    public function toggleStarred(Request $request, \App\Models\CapturedEmail $email)
    {
        if ($email->user_id !== Auth::id()) abort(403);
        $email->update(['is_starred' => $request->is_starred]);
        return response()->json(['success' => true]);
    }
}
