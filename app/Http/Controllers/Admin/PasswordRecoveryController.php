<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PasswordRecoveryRequest;
use App\Models\PasswordRecoveryToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordRecoveryController extends Controller
{
    public function index()
    {
        $requests = PasswordRecoveryRequest::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.password_recoveries.index', compact('requests'));
    }

    public function approve(PasswordRecoveryRequest $recoveryRequest, Request $request)
    {
        if ($recoveryRequest->status !== 'pending') {
            return back()->with('error', 'Request has already been processed.');
        }

        if (!$recoveryRequest->user_id) {
            return back()->with('error', 'Cannot approve request for non-existent user.');
        }

        // Generate cryptographically secure token
        $plainToken = Str::random(64);

        // Store hash in DB
        PasswordRecoveryToken::create([
            'recovery_request_id' => $recoveryRequest->id,
            'user_id' => $recoveryRequest->user_id,
            'token_hash' => Hash::make($plainToken),
            'expires_at' => now()->addMinutes(30),
        ]);

        $recoveryRequest->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
            'approved_ip' => $request->ip(),
        ]);

        // Send Email
        Mail::send('emails.password-reset', [
            'token' => $plainToken,
            'email' => $recoveryRequest->identifier,
            'user' => $recoveryRequest->user
        ], function ($message) use ($recoveryRequest) {
            $message->to($recoveryRequest->identifier)
                ->subject('Permintaan Pemulihan Akun Anda Telah Disetujui');
        });

        return back()->with('success', 'Request approved. Reset email sent.');
    }

    public function reject(PasswordRecoveryRequest $recoveryRequest, Request $request)
    {
        if ($recoveryRequest->status !== 'pending') {
            return back()->with('error', 'Request has already been processed.');
        }

        $recoveryRequest->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Request rejected.');
    }

    public function block(PasswordRecoveryRequest $recoveryRequest, Request $request)
    {
        if ($recoveryRequest->status !== 'pending') {
            return back()->with('error', 'Request has already been processed.');
        }

        $recoveryRequest->update([
            'status' => 'blocked',
            'rejected_at' => now(),
            'rejected_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Request rejected and blocked.');
    }
}
