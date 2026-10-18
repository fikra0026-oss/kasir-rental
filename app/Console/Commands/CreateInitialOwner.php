<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateInitialOwner extends Command
{
    protected $signature = 'app:create-owner';

    protected $description = 'Create the first verified owner account from temporary environment variables';

    public function handle(): int
    {
        if (User::query()->exists()) {
            $this->error('An account already exists; the initial owner can only be created once.');

            return self::FAILURE;
        }

        $attributes = [
            'name' => env('INITIAL_ADMIN_NAME'),
            'email' => env('INITIAL_ADMIN_EMAIL'),
            'password' => env('INITIAL_ADMIN_PASSWORD'),
        ];

        $validator = Validator::make($attributes, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12'],
        ]);

        if ($validator->fails()) {
            $this->error('Set valid INITIAL_ADMIN_NAME, INITIAL_ADMIN_EMAIL, and INITIAL_ADMIN_PASSWORD environment variables. The password must contain at least 12 characters.');

            return self::FAILURE;
        }

        $user = new User;
        $user->name = $attributes['name'];
        $user->email = strtolower($attributes['email']);
        $user->password = $attributes['password'];
        $user->email_verified_at = now();
        $user->role = 'admin';
        $user->save();

        $this->info('Initial owner account created. Remove the INITIAL_ADMIN_* environment variables now.');

        return self::SUCCESS;
    }
}
