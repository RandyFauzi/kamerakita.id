<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\AdminAnnouncementWebPush;
use Illuminate\Support\Facades\Notification;

class SendAnnouncementPush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:announce {title} {message} {--role=all : The user role to target}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a Web Push announcement to users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $title = $this->argument('title');
        $message = $this->argument('message');
        $role = $this->option('role');

        $query = User::whereNotNull('email');
        
        if ($role !== 'all') {
            $query->where('role', $role);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->warn("No users found to send the push notification.");
            return;
        }

        Notification::send($users, new AdminAnnouncementWebPush($title, $message));

        $this->info("Push notification '{$title}' sent successfully to {$users->count()} users.");
    }
}
