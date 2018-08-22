<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ReplyTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function it_has_an_owner()
    {
        $reply = factory('App\Reply')->create();

        $this->assertInstanceOf('App\User', $reply->owner);
    }

    /** @test */
    function it_know_if_it_was_just_published()
    {
        $reply = create('App\Reply');
        $this->assertTrue($reply->wasJustPublished());

        $reply = create('App\Reply', [
            'created_at' => Carbon::now()->subMinute(10)
        ]);
        $this->assertFalse($reply->wasJustPublished());
    }

}
