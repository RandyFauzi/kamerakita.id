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
            ->where('status', '!=', Partner::STATUS_SUSPENDED)
            ->orderBy('full_name')
            ->get(['id', 'mitra_id', 'full_name', 'partner_role', 'email', 'whatsapp_number', 'status']);

        return response()->json(['data' => $parents]);
    }

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => 'required|email|max:100']);
        $partner = Partner::where('partner_role', Partner::ROLE_WORKER)
            ->whereHas('mitraParent', fn ($query) => $query->whereIn('partner_role', Partner::TEAM_PARENT_ROLES))
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
        $this->requireTeamWorker($partner);
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
                Rule::exists('partners', 'id')->where(fn ($query) => $query->whereIn('partner_role', Partner::TEAM_PARENT_ROLES)->where('status', '!=', Partner::STATUS_SUSPENDED)),
            ],
            'phone_key' => 'nullable|string|max:64',
            'account_name' => 'nullable|string|max:128',
        ]);

        $email = strtolower(trim($data['email']));

        $existingPartner = Partner::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($existingPartner) {
            abort_unless(
                $existingPartner->isWorker() && $existingPartner->mitra_parent_id === $data['mitra_parent_id'],
                422,
                'Email sudah terdaftar. Pilih vendor yang menaungi worker tersebut atau koreksi peran akun di panel admin KameraKita.'
            );
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

        abort_if(User::whereRaw('LOWER(email) = ?', [$email])->exists(), 422, 'Email sudah terdaftar pada akun user lain.');

        $parent = Partner::findOrFail($data['mitra_parent_id']);
        abort_unless(in_array($parent->partner_role, Partner::TEAM_PARENT_ROLES, true) && $parent->status !== Partner::STATUS_SUSPENDED, 422, 'Akun utama/mitra tidak valid atau dibekukan.');

        $partner = DB::transaction(function () use ($data, $email, $parent) {
            $bypassBank = $parent->bypassesWorkerBankOnboarding();
            $user = User::create([
                'name' => $data['full_name'],
                'email' => $email,
                'password' => $data['password'],
                'role' => Partner::ROLE_WORKER,
                'email_verified_at' => now(),
                'has_completed_onboarding' => $bypassBank,
                'tos_accepted_at' => $bypassBank ? now() : null,
            ]);

            Partner::query()->where('mitra_id', 'like', 'KMK-%')->orderByDesc('mitra_id')->lockForUpdate()->first(['id']);
            $mitraId = Partner::nextMitraId();
            while (Partner::where('mitra_id', $mitraId)->exists()) {
                $mitraId = 'KMK-'.str_pad(((int) substr($mitraId, 4)) + 1, 3, '0', STR_PAD_LEFT);
            }

            return Partner::create([
                'partner_role' => Partner::ROLE_WORKER,
                'mitra_parent_id' => $parent->id,
                'recruiter_partner_id' => $parent->id,
                'mitra_id' => $mitraId,
                'full_name' => $data['full_name'],
                'whatsapp_number' => $data['whatsapp_number'],
                'email' => $email,
                'has_headstrap' => false,
                'status' => Partner::STATUS_ACTIVE,
                'group_name' => $parent->group_name ?? 'Group A',
                'base_hourly_rate' => $parent->subWorkerHourlyRate(),
                'user_id' => $user->id,
                'bank_name' => $bypassBank ? $parent->bank_name : null,
                'bank_account_number' => $bypassBank ? $parent->bank_account_number : null,
                'bank_account_owner' => $bypassBank ? $parent->bank_account_owner : null,
                'payment_method' => $bypassBank ? ($parent->payment_method ?: 'bank_transfer') : null,
                'airtm_username' => $bypassBank ? $parent->airtm_username : null,
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
        $this->requireTeamWorker($partner);
        $data = $request->validate(['status' => ['required', Rule::in([Partner::STATUS_ACTIVE, Partner::STATUS_INACTIVE, Partner::STATUS_SUSPENDED])]]);

        DB::transaction(function () use ($partner, $data) {
            $partner->update(['status' => $data['status']]);
            $partner->user?->update(['role' => $data['status'] === Partner::STATUS_ACTIVE ? Partner::ROLE_WORKER : 'suspended']);
        });

        Log::info('Private Studio integration changed a partner status.', ['partner_id' => $partner->id, 'status' => $data['status']]);

        return response()->json(['data' => ['id' => $partner->id, 'mitra_id' => $partner->mitra_id, 'email' => $partner->email, 'status' => $partner->status]]);
    }

    public function update(Request $request, Partner $partner): JsonResponse
    {
        $this->requireTeamWorker($partner);
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'whatsapp_number' => 'required|string|max:20',
            'mitra_parent_id' => ['required', 'uuid', Rule::exists('partners', 'id')->where(fn ($query) => $query->whereIn('partner_role', Partner::TEAM_PARENT_ROLES)->where('status', '!=', Partner::STATUS_SUSPENDED))],
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

    public function destroy(Partner $partner): JsonResponse
    {
        $this->requireTeamWorker($partner);

        if ($partner->workers()->exists()) {
            abort(422, 'Tidak dapat menghapus akun yang masih memiliki anggota tim.');
        }

        if ($partner->videoWorkReports()->exists() || $partner->recordings()->exists()) {
            abort(422, 'Tidak dapat menghapus permanen worker yang sudah memiliki riwayat laporan atau rekaman. Nonaktifkan status akun untuk membekukan.');
        }

        $partnerId = $partner->id;
        $mitraId = $partner->mitra_id;
        $email = $partner->email;

        DB::transaction(function () use ($partner) {
            $user = $partner->user;
            $partner->delete();
            $user?->delete();
        });

        Log::info('Private Studio integration deleted a partner and user.', [
            'partner_id' => $partnerId,
            'mitra_id' => $mitraId,
            'email' => $email,
        ]);

        return response()->json([
            'message' => 'Akun worker dan user KameraKita berhasil dihapus.',
            'deleted' => true,
        ]);
    }

    private function requireTeamWorker(Partner $partner): void
    {
        abort_unless($partner->isWorker() && $partner->mitraParent?->isTeamParent(), 404);
    }
}
