<?php


use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_user_command()
    {
        $email = fake()->email;

        $this->artisan('app:create-user')
            ->expectsQuestion('Define an Name', fake()->name)
            ->expectsQuestion('Define an Email', $email)
            ->expectsQuestion('Define a Password', '123456')
            ->assertExitCode(1);

        $this->assertNotNull(User::whereEmail($email));
    }

}
