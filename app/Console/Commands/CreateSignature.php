<?php

namespace App\Console\Commands;

use App\Providers\AuthorizationProvider;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Exception;

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

        try {
            $tokenAttributes = AuthorizationProvider::getTokenProperties($accessKey);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());

            return 0;
        }

        $timeStamp = Carbon::now()->utc()->toIso8601String();

        $this->info('ts: ' . $timeStamp);

        $signature = AuthorizationProvider::signToken(
            appKey: $tokenAttributes['appKey'],
            appSecret: $tokenAttributes['appSecret'],
            timeStamp: $timeStamp,
        );

        $this->info('Signature: ' . $signature);

        return 1;
    }
}
