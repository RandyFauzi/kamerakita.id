<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CapturedEmail;

class MailboxController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function index()
    {
        return view('mailbox.index');
    }

    public function fetchEmails(Request $request)
    {
        $query = CapturedEmail::query();
        
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('sender_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter')) {
            $filter = $request->filter;
            if ($filter === 'unread') {
                $query->where('is_read', false);
            } elseif ($filter === 'read') {
                $query->where('is_read', true);
            } elseif ($filter === 'starred') {
                $query->where('is_starred', true);
            }
        }

        $emails = $query->select(['id', 'user_id', 'sender_address', 'subject', 'received_at', 'is_read', 'is_starred'])
            ->orderBy('received_at', 'desc')
            ->paginate(50);
            
        return response()->json($emails);
    }

    public function showEmail(CapturedEmail $email)
    {
        $this->authorize('view', $email);

        return response()->json([
            'id' => $email->id,
            'subject' => mb_convert_encoding((string) $email->subject, 'UTF-8', 'UTF-8'),
            'sender_address' => mb_convert_encoding((string) $email->sender_address, 'UTF-8', 'UTF-8'),
            'received_at' => $email->received_at,
            'sanitized_content' => $email->sanitized_content,
        ]);
    }

    public function toggleRead(Request $request, CapturedEmail $email)
    {
        $this->authorize('update', $email);

        $validated = $request->validate([
            'is_read' => 'required|boolean',
        ]);

        $email->update($validated);
        return response()->json(['success' => true]);
    }

    public function toggleStarred(Request $request, CapturedEmail $email)
    {
        $this->authorize('update', $email);

        $validated = $request->validate([
            'is_starred' => 'required|boolean',
        ]);

        $email->update($validated);
        return response()->json(['success' => true]);
    }
}
