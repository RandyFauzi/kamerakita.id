<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ManageAdminUsersController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $roleFilter = $request->input('role');
        $roleOptions = User::internalRoleOptions();

        $admins = User::query()
            ->whereIn('role', User::INTERNAL_ROLES)
            ->when($roleFilter && in_array($roleFilter, User::INTERNAL_ROLES, true), function ($query) use ($roleFilter): void {
                $query->where('role', $roleFilter);
            })
            ->when($search, function ($query, string $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin-users.index', compact('admins', 'search', 'roleFilter', 'roleOptions'));
    }

    public function create(): View
    {
        $roleOptions = User::internalRoleOptions();

        return view('admin-users.create', compact('roleOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(User::INTERNAL_ROLES)],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $created = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'email_verified_at' => now(),
        ]);

        \App\Services\ActivityLogger::log(
            'admin_user.create',
            "Menambahkan akun internal {$created->name} ({$created->email}) sebagai {$created->roleLabel()}."
        );

        return redirect()->route('admin-users.index')->with('success', "Akun {$created->roleLabel()} berhasil dibuat.");
    }

    public function edit(User $adminUser): View
    {
        $this->ensureManagedInternalUser($adminUser);
        $roleOptions = User::internalRoleOptions();

        return view('admin-users.edit', compact('adminUser', 'roleOptions'));
    }

    public function update(Request $request, User $adminUser): RedirectResponse
    {
        $this->ensureManagedInternalUser($adminUser);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($adminUser->id),
            ],
            'role' => ['required', Rule::in(User::INTERNAL_ROLES)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if ($request->user()->is($adminUser) && $validated['role'] !== $adminUser->role) {
            return back()->withInput()->with('error', 'Anda tidak dapat mengubah role akun yang sedang Anda gunakan.');
        }

        $adminUser->name = $validated['name'];
        $adminUser->email = $validated['email'];
        $adminUser->role = $validated['role'];

        if (! empty($validated['password'])) {
            $adminUser->password = Hash::make($validated['password']);
        }

        $adminUser->save();

        \App\Services\ActivityLogger::log(
            'admin_user.update',
            "Memperbarui akun internal {$adminUser->name} ({$adminUser->email}, role: {$adminUser->roleLabel()})."
        );

        return redirect()->route('admin-users.index')->with('success', "Akun {$adminUser->roleLabel()} berhasil diperbarui.");
    }

    public function destroy(Request $request, User $adminUser): RedirectResponse
    {
        $this->ensureManagedInternalUser($adminUser);

        if ($request->user()->is($adminUser)) {
            return redirect()->route('admin-users.index')->with('error', 'Anda tidak dapat menghapus akun internal yang sedang digunakan.');
        }

        $label = $adminUser->roleLabel();
        $email = $adminUser->email;
        $adminUser->delete();

        \App\Services\ActivityLogger::log(
            'admin_user.delete',
            "Menghapus akun internal {$email} ({$label})."
        );

        return redirect()->route('admin-users.index')->with('success', 'Akun internal berhasil dihapus.');
    }

    private function ensureManagedInternalUser(User $user): void
    {
        abort_unless(in_array($user->role, User::INTERNAL_ROLES, true), 404);
    }
}
