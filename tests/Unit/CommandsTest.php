<?php


use Tests\TestCase;

class CommandsTest extends TestCase
{
    public function test_create_app_key_signature_command()
    {
        $this->artisan('app:create-signature')
            ->expectsOutput('Laravel API Service')
            ->expectsOutput('Signed access token generation script.')
            ->expectsOutput('Before moving ahead, define your access key in ./config/laravel-mail-api-token.php')
            ->expectsQuestion('Inform the Token Access Key', 'tests')
            ->expectsOutputToContain('ts')
            ->expectsOutputToContain('Signature:')
            ->assertExitCode(1);
    }

    public function test_create_app_key_invalid_access_key()
    {
        $this->artisan('app:create-signature')
            ->expectsOutput('Laravel API Service')
            ->expectsOutput('Signed access token generation script.')
            ->expectsOutput('Before moving ahead, define your access key in ./config/laravel-mail-api-token.php')
            ->expectsQuestion('Inform the Token Access Key', 'x')
            ->expectsOutput('Invalid access token.')
            ->assertExitCode(0);
    }
}
