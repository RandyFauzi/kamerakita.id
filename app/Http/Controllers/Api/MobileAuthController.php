<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MobileAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with('partner')->where('email', $request->email)->first();

        // 1. Validasi Kredensial
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        // 2. Validasi Status Partner (Worker aktif)
        if (!$user->partner || $user->partner->status !== Partner::STATUS_ACTIVE) {
            return response()->json(['message' => 'Akun Kontributor tidak aktif atau Anda belum terdaftar sebagai Partner.'], 403);
        }

        // 3. Generate Sanctum Token
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'partner_id' => $user->partner->id,
                'mitra_id' => $user->partner->mitra_id,
            ]
        ]);
    }
}
