<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Providers\AuthorizationProvider;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSignature extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-signature';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates signature for http requests';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Laravel API Service');
        $this->info('Signed access token generation script.');
        $this->warn('Before moving ahead, define your access key in ./config/laravel-mail-api-token.php');
        $this->info('---------------------------------------');

        $accessKey = $this->ask('Inform the Token Access Key');

        $tokenAttributes = AuthorizationProvider::getTokenProperties($accessKey);
        $timezone = Carbon::now()->toIso8601String();
        $this->info('tz: ' . $timezone);
        $signature = AuthorizationProvider::signToken(
            token: $tokenAttributes['appKey'],
            timeStamp: $timezone,
            secret: $tokenAttributes['appSecret'],
        );

        $this->info($signature);

        return 1;
    }
}
