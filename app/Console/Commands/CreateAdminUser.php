<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'user:create-admin
        {email : Admin email address}
        {--name=Admin : Admin display name}
        {--password= : Admin password. If omitted, the command asks interactively}';

    protected $description = 'Create or update a full-access admin account without using the superadmin role.';

    public function handle(): int
    {
        $email = strtolower((string) $this->argument('email'));
        $name = (string) $this->option('name');
        $password = $this->option('password') ?: $this->secret('Admin password');

        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ], [
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', Password::min(8)],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $user = User::query()->firstOrNew(['email' => $email]);
        $user->name = $name;
        $user->password = Hash::make($password);
        $user->role = 'admin';
        $user->email_verified_at = $user->email_verified_at ?: now();
        $user->save();

        $this->info("Admin account ready: {$user->email}");

        return self::SUCCESS;
    }
}
