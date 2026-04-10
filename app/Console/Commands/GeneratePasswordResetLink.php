<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

class GeneratePasswordResetLink extends Command
{
    protected $signature = 'password:link {email}';

    protected $description = 'Generate a password reset link for a user';

    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User with email {$email} not found.");

            return self::FAILURE;
        }

        $token = Password::broker()->createToken($user);

        $url = URL::route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        $this->info("Password reset link for {$email}:");
        $this->line($url);

        return self::SUCCESS;
    }
}
