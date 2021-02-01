<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Arr;

use App\Models\InquiryForm;
use App\Models\Livestream;
use App\Models\Tag;

class InquiryFormTest extends TestCase
{

    /** @test **/
    public function an_inquiry_form_has_a_livestreams_attribute()
    {
        $inquiry_form = InquiryForm::factory()->create();
        $livestream = Livestream::factory()->create();
        $past_livestream = Livestream::factory()->create([
            'start_date' => now()->subDays(1),
        ]);

        $this->assertNotNull($inquiry_form->livestreams);
        $this->assertTrue($inquiry_form->livestreams->contains('id', $livestream->id));
        $this->assertFalse($inquiry_form->livestreams->contains('id', $past_livestream->id));

        $tag = Tag::factory()->create();
        $livestream2 = Livestream::factory()->create();

        $inquiry_form->addTag($tag);
        $livestream2->addTag($tag);

        $inquiry_form->refresh();

        $this->assertTrue($inquiry_form->livestreams->contains('id', $livestream2->id));
        $this->assertFalse($inquiry_form->livestreams->contains('id', $livestream->id));

        $data = InquiryForm::find($inquiry_form->id)->toArray();
        $this->assertNotNull(Arr::get($data, 'livestreams'));
    }
}
