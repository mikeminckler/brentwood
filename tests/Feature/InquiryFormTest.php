<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Arr;

use Tests\Feature\ContentElementsTestTrait;

use App\Models\InquiryForm;
use App\Models\User;
use App\Models\Page;
use App\Models\Tag;

class InquiryFormTest extends TestCase
{

    use ContentElementsTestTrait;

    protected function getClassname()
    {
        return 'inquiry-form';
    }

    /** @test **/
    public function an_inquiry_form_can_be_created()
    {
        $tag = Tag::factory()->create();

        $input = $this->createContentElement(InquiryForm::factory())->toArray();
        $input['id'] = 0;
        $input['content_id'] = 0;
        $input['content_type'] = null;
        $input['content.id'] = 0;
        $input['content']['tags'] = [
            $tag,
        ];

        $page = Page::factory()->create();
        $input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];

        $this->json('POST', route('content-elements.store'), [])
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route('content-elements.store'), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'pivot.contentable_id',
                 'pivot.contentable_type',
             ]);

        $this->json('POST', route('content-elements.store'), ['pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route('content-elements.store'), ['pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                 'type',
             ]);

        $this->json('POST', route('content-elements.store'), ['type' => 'blog-list', 'pivot' => ['contentable_id' => $page->id, 'contentable_type' => 'page']])
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'pivot.sort_order',
                'pivot.unlisted',
                'pivot.expandable',
             ]);

        $content = $input['content'];
        $input['content'] = [];

        $this->json('POST', route('content-elements.store'), $input)
             ->assertStatus(422)
             ->assertJsonValidationErrors([
                'content.show_student_info',
                'content.show_interests',
                'content.show_livestreams',
                'content.show_livestreams_first',
             ]);

        $input['content'] = $content;

        $this->withoutExceptionHandling();
        $this->json('POST', route('content-elements.store'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'success' => 'Inquiry Form Saved',
             ]);

        $inquiry_form = InquiryForm::all()->last();
        $this->assertEquals(Arr::get($input, 'content.header'), $inquiry_form->header);
        $this->assertEquals(Arr::get($input, 'content.body'), $inquiry_form->body);
        $this->assertEquals(Arr::get($input, 'content.show_student_info'), $inquiry_form->show_student_info);
        $this->assertEquals(Arr::get($input, 'content.show_interests'), $inquiry_form->show_interests);
        $this->assertEquals(Arr::get($input, 'content.show_livestreams'), $inquiry_form->show_livestreams);
        $this->assertEquals(Arr::get($input, 'content.show_livestreams_first'), $inquiry_form->show_livestreams_first);
        $this->assertTrue($inquiry_form->tags->contains('id', $tag->id));
    }

}
