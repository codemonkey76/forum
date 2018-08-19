<?php

namespace Tests\Feature;

use Tests\TestCase;

class SpamTest extends TestCase
{

    /** @test */
    function it_validates_spam()
    {
        $spam = new Spam();
        $this->assertFalse($spam->detect('Innocent Reply here'));
        $this->assertTrue($spam->detect('yahoo customer suppoert'));
        
    }
}
