<?php

namespace Tests\Unit;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use Illuminate\Support\Collection;

use App\ContentElement;
use App\TextBlock;
use App\Page;

trait ContentElementsTestTrait
{

    protected abstract function getModel();
    protected abstract function getClassname();

    /** @test **/
    public function a_page_can_have_many_content_elements()
    {
        $content_element = factory(ContentElement::class)->states($this->getClassname(), 'text-block')->create();
        $page = $content_element->{Str::plural($this->getClassname())}->first();

        $page->refresh();

        $this->assertNotNull($page->contentElements);
        $this->assertTrue($page->contentElements->contains('id', $content_element->id));
    }

    /** @test **/
    public function a_page_can_save_its_content_elements()
    {
        $page = $this->getModel();
        $content_element_input = factory(ContentElement::class)->states($this->getClassname(), 'text-block')->raw();
        $content_element_input['type'] = 'text-block';
        $content_element_input['content'] = factory(TextBlock::class)->raw();
        $content_element_input['pivot'] = [
            'contentable_id' => $page->id,
            'contentable_type' => get_class($page),
            'sort_order' => 1,
            'unlisted' => false,
            'expandable' => false,
        ];
        $input = [
            'content_elements' => [
                $content_element_input,
            ],
        ];

        $page->saveContentElements($input);
        $page->refresh();

        $this->assertEquals(1, $page->contentElements->count());
        $content_element = $page->contentElements->first();
    }

    /** @test **/
    public function a_page_can_get_its_content_elements()
    {
        // this checks for the proper grouping of content elements by UUID
        $page = factory(get_class($this->getModel()))->states('published')->create();

        $published_content_element = factory(ContentElement::class)->states($this->getClassname(), 'text-block')->create([
            'version_id' => $page->published_version_id,
        ]);

        $published_content_element->{Str::plural($this->getClassname())}()->detach();
        $published_content_element->{Str::plural($this->getClassname())}()->attach($page, ['sort_order' => 1, 'unlisted' => false, 'expandable' => false]);

        $unpublished_content_element = factory(ContentElement::class)->states($this->getClassname(), 'text-block')->create([
            'version_id' => $page->draft_version_id,
        ]);

        $unpublished_content_element->{Str::plural($this->getClassname())}()->detach();
        $unpublished_content_element->{Str::plural($this->getClassname())}()->attach($page, ['sort_order' => 1, 'unlisted' => false, 'expandable' => false]);

        $page->refresh();
        $this->assertNotNull($page->content_elements);
        $this->assertInstanceOf(Collection::class, $page->content_elements);
        $this->assertTrue($page->content_elements->contains('id', $unpublished_content_element->id));
        $this->assertTrue($page->content_elements->contains('id', $published_content_element->id));
    }

    /** @test **/
    public function a_page_can_get_its_published_content_elements()
    {
        $content_element = factory(ContentElement::class)->states($this->getClassname(), 'text-block')->create();
        $this->assertEquals(1, $content_element->{Str::plural($this->getClassname())}()->count());
        $page = $content_element->{Str::plural($this->getClassname())}()->first();
        $content_element->version_id = $page->getDraftVersion()->id;
        $content_element->save();

        $this->assertTrue($page->contentElements->contains('id', $content_element->id));
        $this->assertTrue($page->content_elements->contains('id', $content_element->id));
        $this->assertEquals($page->getDraftVersion()->id, $content_element->version_id);

        $page->publish();
        $page->refresh();

        $content_element->refresh();
        $this->assertNotNull($content_element->published_at);
        $this->assertTrue($page->published_content_elements->contains('id', $content_element->id));

        $unlisted_content_element = factory(ContentElement::class)->states($this->getClassname(), 'unlisted', 'text-block')->create([
            'version_id' => $page->published_version_id,
        ]);

        $page->contentElements()->attach($unlisted_content_element, ['sort_order' => 1, 'unlisted' => true, 'expandable' => false]);

        $unpublished_content_element = factory(ContentElement::class)->states($this->getClassname(), 'text-block')->create([
            'version_id' => $page->draft_version_id,
        ]);

        $page->contentElements()->attach($unpublished_content_element, ['sort_order' => 2, 'unlisted' => false, 'expandable' => false,]);

        $this->assertTrue($page->contentElements->contains('id', $content_element->id));
        $this->assertTrue($page->contentElements->contains('id', $unpublished_content_element->id));
        $this->assertTrue($page->contentElements->contains('id', $unlisted_content_element->id));

        $page->refresh();
        $this->assertFalse($page->published_content_elements->contains('id', $unlisted_content_element->id));
        $this->assertFalse($page->published_content_elements->contains('id', $unpublished_content_element->id));
    }

    /** @test **/
    public function a_page_can_get_its_preview_content_elements()
    {
        $this->signInAdmin();
        session()->put('editing', true);
        $content_element = factory(ContentElement::class)->states($this->getClassname(), 'text-block')->create();
        $this->assertEquals(1, $content_element->{Str::plural($this->getClassname())}()->count());
        $page = $content_element->{Str::plural($this->getClassname())}()->first();
        $content_element->version_id = $page->getDraftVersion()->id;
        $content_element->save();

        $this->assertTrue($page->contentElements->contains('id', $content_element->id));
        $this->assertTrue($page->content_elements->contains('id', $content_element->id));
        $this->assertEquals($page->getDraftVersion()->id, $content_element->version_id);
        $this->assertEquals(0, $page->pivot->unlisted);

        $page->publish();
        $page->refresh();
        $content_element->refresh();

        $this->assertNotNull($content_element->published_at);
        $this->assertEquals(1, $page->contentElements->count());
        $this->assertTrue(session()->get('editing'));
        $this->assertTrue($page->contentElements->contains('id', $content_element->id));
        $this->assertTrue($page->preview_content_elements->contains('id', $content_element->id));

        $unlisted_content_element = factory(ContentElement::class)->states($this->getClassname(), 'unlisted', 'text-block')->create([
            'version_id' => $page->published_version_id,
        ]);

        $page->contentElements()->attach($unlisted_content_element, ['sort_order' => 1, 'unlisted' => true, 'expandable' => false]);

        $unpublished_content_element = factory(ContentElement::class)->states($this->getClassname(), 'text-block')->create([
            'version_id' => $page->draft_version_id,
        ]);

        $page->contentElements()->attach($unpublished_content_element, ['sort_order' => 2, 'unlisted' => false, 'expandable' => false]);

        $this->assertTrue($page->contentElements->contains('id', $content_element->id));
        $this->assertTrue($page->contentElements->contains('id', $unpublished_content_element->id));
        $this->assertTrue($page->contentElements->contains('id', $unlisted_content_element->id));

        $page->refresh();
        $this->assertFalse($page->preview_content_elements->contains('id', $unlisted_content_element->id));
        $this->assertTrue($page->preview_content_elements->contains('id', $content_element->id));
        $this->assertTrue($page->preview_content_elements->contains('id', $unpublished_content_element->id));
    }

    /** @test **/
    public function a_page_has_a_type_attribute()
    {
        $page = $this->getModel();

        $this->assertNotNull($page->type);
        $this->assertEquals(Str::kebab(class_basename($this->getModel())), $page->type);
    }


}
