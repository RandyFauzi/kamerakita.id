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

        $admins = User::query()
            ->where('role', 'admin')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin-users.index', compact('admins', 'search'));
    }

    public function create(): View
    {
        return view('admin-users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin-users.index')->with('success', 'Akun admin berhasil dibuat.');
    }

    public function edit(User $adminUser): View
    {
        $this->ensureManagedAdmin($adminUser);

        return view('admin-users.edit', compact('adminUser'));
    }

    public function update(Request $request, User $adminUser): RedirectResponse
    {
        $this->ensureManagedAdmin($adminUser);

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
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $adminUser->name = $validated['name'];
        $adminUser->email = $validated['email'];
        $adminUser->role = 'admin';

        if (! empty($validated['password'])) {
            $adminUser->password = Hash::make($validated['password']);
        }

        $adminUser->save();

        return redirect()->route('admin-users.index')->with('success', 'Akun admin berhasil diperbarui.');
    }

    public function destroy(Request $request, User $adminUser): RedirectResponse
    {
        $this->ensureManagedAdmin($adminUser);

        if ($request->user()->is($adminUser)) {
            return redirect()->route('admin-users.index')->with('error', 'Anda tidak dapat menghapus akun admin yang sedang digunakan.');
        }

        $adminUser->delete();

        return redirect()->route('admin-users.index')->with('success', 'Akun admin berhasil dihapus.');
    }

    private function ensureManagedAdmin(User $user): void
    {
        abort_unless($user->role === 'admin', 404);
    }
}
