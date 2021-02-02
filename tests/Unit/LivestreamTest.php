<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\Inquiry;
use App\Models\Livestream;

class LivestreamTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_example()
    {
        $this->assertTrue(true);
    }

    /** @test **/
    public function a_livestream_can_belong_to_many_inquiries()
    {
        $inquiry = Inquiry::factory()->create();
        $livestream = Livestream::factory()->create();

        $inquiry->saveLivestreams(['livestream' => $livestream]);

        $livestream->refresh();
        $this->assertEquals(1, $livestream->inquiries()->count());
        $this->assertEquals($inquiry->id, $livestream->inquiries()->first()->id);
    }

    /** @test **/
    public function a_livestream_has_a_date_attribute()
    {
        $livestream = Livestream::factory()->create();
        $this->assertNotNull($livestream->date);
        $this->assertEquals($livestream->start_date->timezone('America/Vancouver')->format('l F jS g:ia'), $livestream->date);
    }
}
