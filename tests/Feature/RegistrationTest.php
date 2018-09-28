<?php

namespace Tests\Feature;

use App\Mail\PleaseConfirmYourEmail;
use App\User;
use Illuminate\Auth\Events\Registered;
use Mail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class RegistrationTest extends TestCase {

    use DatabaseMigrations;

    /** @test */
    public function a_confirmation_email_is_sent_upon_registration()
    {
        Mail::fake();


        event(new Registered(create(User::class)));
        Mail::assertSent(PleaseConfirmYourEmail::class);
    }

    /** @test */
    function users_can_fully_confirm_their_email_addresses()
    {
        $this->post('/register', [
            'name'                  => 'John',
            'email'                 => 'john@example.com',
            'password'              => 'foobar',
            'password_confirmation' => 'foobar'
        ]);

        $user = User::whereName('John')->first();

        $this->assertFalse($user->confirmed);
        $this->assertNotNull($user->confirmation_token);

        $response = $this->get('/register/confirm?token=' . $user->confirmation_token);

        $this->assertTrue($user->fresh()->confirmed);

        $response->assertRedirect('/threads');
    }
}

