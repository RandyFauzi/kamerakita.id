<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Services\StoreEvidenceImageService;
use App\Services\EvidenceFileBackupService;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $request->user()->load('partner');

        return view('profile.edit', [
            'user' => $request->user(),
            'partner' => $request->user()->partner,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $user->fill([
            'name' => $validated['name'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                app(EvidenceFileBackupService::class)->delete($user->avatar);
                if (Storage::disk('evidence')->exists($user->avatar)) {
                    Storage::disk('evidence')->delete($user->avatar);
                } elseif (Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
            }
            
            $imageStorage = app(StoreEvidenceImageService::class);
            $path = $imageStorage->store($request->file('avatar'), 'avatars');
            app(EvidenceFileBackupService::class)->backup($path);

            $user->avatar = $path;
        }

        $user->save();

        if ($user->partner) {
            $partnerData = collect($validated)
                ->only([
                    'full_name',
                    'nik',
                    'whatsapp_number',
                    'full_address',
                    'bank_name',
                    'bank_account_number',
                    'bank_account_owner',
                    'smartphone_type',
                    'has_headstrap',
                ])
                ->all();

            $partnerData['account_number'] = $partnerData['bank_account_number'] ?? null;
            $partnerData['account_owner_name'] = $partnerData['bank_account_owner'] ?? null;

            $user->partner->update($partnerData);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
