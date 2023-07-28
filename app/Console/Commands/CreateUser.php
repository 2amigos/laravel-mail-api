<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new API user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('Define an Name');
        $email = $this->ask('Define an Email');
        $password = $this->secret('Define a Password');

        if (User::where('email', $email)->exists()) {
            $this->error('User with given email already exists!');

            return 0;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => Carbon::now()->toDateTimeString(),
        ]);

        return 1;
    }
}
