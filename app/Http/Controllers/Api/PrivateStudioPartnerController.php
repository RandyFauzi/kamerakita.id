<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PrivateStudioPartnerController extends Controller
{
    public function parents(): JsonResponse
    {
        $parents = Partner::query()
            ->whereIn('partner_role', Partner::TEAM_PARENT_ROLES)
            ->where('status', Partner::STATUS_ACTIVE)
            ->orderBy('full_name')
            ->get(['id', 'mitra_id', 'full_name', 'partner_role', 'email']);

        return response()->json(['data' => $parents]);
    }

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => 'required|email|max:100']);
        $partner = Partner::where('partner_role', Partner::ROLE_MITRA)
            ->whereRaw('LOWER(email) = ?', [strtolower($data['email'])])->first();

        return response()->json(['data' => $partner ? [
            'id' => $partner->id,
            'mitra_id' => $partner->mitra_id,
            'email' => $partner->email,
            'status' => $partner->status,
            'partner_role' => $partner->partner_role,
        ] : null]);
    }

    public function show(Partner $partner): JsonResponse
    {
        abort_unless($partner->partner_role === Partner::ROLE_MITRA, 404);
        return response()->json(['data' => [
            'id' => $partner->id,
            'mitra_id' => $partner->mitra_id,
            'email' => $partner->email,
            'status' => $partner->status,
            'full_name' => $partner->full_name,
            'whatsapp_number' => $partner->whatsapp_number,
            'mitra_parent_id' => $partner->mitra_parent_id,
        ]]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:100',
            'password' => 'required|string|min:12|max:128',
            'whatsapp_number' => 'required|string|max:20',
            'mitra_parent_id' => [
                'required',
                'uuid',
                Rule::exists('partners', 'id')->where(fn ($query) => $query->whereIn('partner_role', Partner::TEAM_PARENT_ROLES)->where('status', Partner::STATUS_ACTIVE)),
            ],
            'phone_key' => 'nullable|string|max:64',
            'account_name' => 'nullable|string|max:128',
        ]);

        $email = strtolower(trim($data['email']));

        // Idempotensi: jika mitra dengan email ini sudah ada, kembalikan mitra yang sudah ada
        $existingPartner = Partner::where('partner_role', Partner::ROLE_MITRA)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($existingPartner) {
            $parent = $existingPartner->mitraParent;
            return response()->json(['data' => [
                'id' => $existingPartner->id,
                'mitra_id' => $existingPartner->mitra_id,
                'email' => $existingPartner->email,
                'status' => $existingPartner->status,
                'partner_role' => $existingPartner->partner_role,
                'parent' => $parent ? ['id' => $parent->id, 'name' => $parent->full_name] : null,
            ]], 200);
        }

        // Jika email sudah terpakai di User atau Partner peran lain (bukan mitra), tolak
        abort_if(Partner::whereRaw('LOWER(email) = ?', [$email])->exists(), 422, 'Email sudah terdaftar pada peran partner lain.');
        abort_if(User::whereRaw('LOWER(email) = ?', [$email])->exists(), 422, 'Email sudah terdaftar pada akun user lain.');

        $parent = Partner::findOrFail($data['mitra_parent_id']);
        abort_unless(in_array($parent->partner_role, Partner::TEAM_PARENT_ROLES, true) && $parent->status === Partner::STATUS_ACTIVE, 422, 'Akun utama tidak aktif atau tidak valid.');

        $partner = DB::transaction(function () use ($data, $email, $parent) {
            $user = User::create([
                'name' => $data['full_name'],
                'email' => $email,
                'password' => $data['password'],
                'role' => Partner::ROLE_MITRA,
                'email_verified_at' => now(),
                'has_completed_onboarding' => true,
                'tos_accepted_at' => now(),
            ]);

            Partner::query()->where('mitra_id', 'like', 'KMK-%')->orderByDesc('mitra_id')->lockForUpdate()->first(['id']);
            $mitraId = Partner::nextMitraId();
            while (Partner::where('mitra_id', $mitraId)->exists()) {
                $mitraId = 'KMK-'.str_pad(((int) substr($mitraId, 4)) + 1, 3, '0', STR_PAD_LEFT);
            }

            return Partner::create([
                'partner_role' => Partner::ROLE_MITRA,
                'mitra_parent_id' => $parent->id,
                'mitra_id' => $mitraId,
                'full_name' => $data['full_name'],
                'whatsapp_number' => $data['whatsapp_number'],
                'email' => $email,
                'has_headstrap' => false,
                'status' => Partner::STATUS_ACTIVE,
                'group_name' => 'Group A',
                'base_hourly_rate' => Partner::MITRA_WORKER_HOURLY_RATE_IDR,
                'user_id' => $user->id,
                'bypass_worker_bank_onboarding' => true,
            ]);
        });

        Log::info('Private Studio integration created a KameraKita partner.', [
            'partner_id' => $partner->id,
            'mitra_id' => $partner->mitra_id,
            'email' => $partner->email,
            'parent_id' => $parent->id,
        ]);

        return response()->json(['data' => [
            'id' => $partner->id,
            'mitra_id' => $partner->mitra_id,
            'email' => $partner->email,
            'status' => $partner->status,
            'partner_role' => $partner->partner_role,
            'parent' => ['id' => $parent->id, 'name' => $parent->full_name],
        ]], 201);
    }

    public function setStatus(Request $request, Partner $partner): JsonResponse
    {
        abort_unless($partner->partner_role === Partner::ROLE_MITRA, 404);
        $data = $request->validate(['status' => ['required', Rule::in([Partner::STATUS_ACTIVE, Partner::STATUS_INACTIVE, Partner::STATUS_SUSPENDED])]]);

        DB::transaction(function () use ($partner, $data) {
            $partner->update(['status' => $data['status']]);
            $partner->user?->update(['role' => $data['status'] === Partner::STATUS_ACTIVE ? Partner::ROLE_MITRA : 'suspended']);
        });

        Log::info('Private Studio integration changed a partner status.', ['partner_id' => $partner->id, 'status' => $data['status']]);

        return response()->json(['data' => ['id' => $partner->id, 'mitra_id' => $partner->mitra_id, 'email' => $partner->email, 'status' => $partner->status]]);
    }

    public function update(Request $request, Partner $partner): JsonResponse
    {
        abort_unless($partner->partner_role === Partner::ROLE_MITRA, 404);
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'whatsapp_number' => 'required|string|max:20',
            'mitra_parent_id' => ['required', 'uuid', Rule::exists('partners', 'id')->where(fn ($query) => $query->whereIn('partner_role', Partner::TEAM_PARENT_ROLES)->where('status', Partner::STATUS_ACTIVE))],
        ]);

        DB::transaction(function () use ($partner, $data) {
            $partner->update($data);
            $partner->user?->update(['name' => $data['full_name']]);
        });

        Log::info('Private Studio integration updated a partner profile.', ['partner_id' => $partner->id]);

        return response()->json(['data' => [
            'id' => $partner->id,
            'mitra_id' => $partner->mitra_id,
            'email' => $partner->email,
            'status' => $partner->status,
            'partner_role' => $partner->partner_role,
            'full_name' => $partner->full_name,
            'whatsapp_number' => $partner->whatsapp_number,
            'mitra_parent_id' => $partner->mitra_parent_id,
        ]]);
    }
}
