<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MailboxController extends Controller
{
    public function index()
    {
        $emails = Auth::user()->capturedEmails()
            ->select(['id', 'user_id', 'sender_address', 'subject', 'received_at', 'is_read', 'is_starred', 'message_content'])
            ->orderBy('received_at', 'desc')
            ->paginate(50);
        return view('mailbox.index', compact('emails'));
    }

    public function toggleRead(Request $request, \App\Models\CapturedEmail $email)
    {
        $this->authorize('update', $email);

        $validated = $request->validate([
            'is_read' => 'required|boolean',
        ]);

        $email->update($validated);
        return response()->json(['success' => true]);
    }

    public function toggleStarred(Request $request, \App\Models\CapturedEmail $email)
    {
        $this->authorize('update', $email);

        $validated = $request->validate([
            'is_starred' => 'required|boolean',
        ]);

        $email->update($validated);
        return response()->json(['success' => true]);
    }
}
