<?php

namespace Tests\Unit;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

use App\Models\ContentElement;
use App\Models\TextBlock;
use App\Models\Page;
use App\Models\User;
use App\Models\Role;

trait ContentElementsTestTrait
{
    abstract protected function getModel();
    abstract protected function getClassname();

    /** @test **/
    public function a_page_can_have_many_content_elements()
    {
        $content_element = $this->createContentElement(TextBlock::factory(), $this->getModel());
        $page = $content_element->{Str::plural($this->getClassname())}->first();

        $page->refresh();

        $this->assertNotNull($page->contentElements);
        $this->assertTrue($page->contentElements->contains('id', $content_element->id));
    }

    /** @test **/
    public function a_page_has_a_full_type_attribute()
    {
        $page = $this->getModel();   
        $this->assertNotNull($page->full_type);
        $this->assertEquals(get_class($page), $page->full_type);
    }

    /** @test **/
    public function a_page_can_save_its_content_elements()
    {
        $page = $this->getModel();
        $content_element_input = $this->createContentElement(TextBlock::factory(), $this->getModel())->toArray();
        $content_element_input['id'] = '0';
        $content_element_input['type'] = 'text-block';
        $content_element_input['content'] = TextBlock::factory()->raw();
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
        $page = get_class($this->getModel())::factory()->published()->create();
        
        $published_content_element = ContentElement::factory()->for(TextBlock::factory(), 'content')->create();

        $published_content_element->{Str::plural($this->getClassname())}()->detach();
        $published_content_element->{Str::plural($this->getClassname())}()->attach($page, ['sort_order' => 1, 'unlisted' => false, 'expandable' => false, 'version_id' => $page->draft_version_id]);

        $unpublished_content_element = ContentElement::factory()->for(TextBlock::factory(), 'content')->create();

        $unpublished_content_element->{Str::plural($this->getClassname())}()->detach();
        $unpublished_content_element->{Str::plural($this->getClassname())}()->attach($page, ['sort_order' => 1, 'unlisted' => false, 'expandable' => false, 'version_id' => $page->draft_version_id]);

        $page->refresh();
        $this->assertNotNull($page->content_elements);
        $this->assertInstanceOf(Collection::class, $page->content_elements);
        $this->assertTrue($page->content_elements->contains('id', $unpublished_content_element->id));
        $this->assertTrue($page->content_elements->contains('id', $published_content_element->id));
    }

    /** @test **/
    public function a_page_can_get_its_published_content_elements()
    {
        $content_element = $this->createContentElement(TextBlock::factory(), $this->getModel());
        $this->assertEquals(1, $content_element->{Str::plural($this->getClassname())}()->count());
        $page = $content_element->{Str::plural($this->getClassname())}()->first();
        $content_element->save();

        $this->assertTrue($page->contentElements->contains('id', $content_element->id));
        $this->assertTrue($page->content_elements->contains('id', $content_element->id));
        $this->assertEquals($page->getDraftVersion()->id, $content_element->contentables->first()->version->id);

        $page->publish();
        $page->refresh();

        $content_element->refresh();
        $this->assertNotNull($content_element->contentables->first()->version->published_at);
        $this->assertTrue($page->published_content_elements->contains('id', $content_element->id));

        $unlisted_content_element = ContentElement::factory()->for(TextBlock::factory(), 'content')->create();

        $page->contentElements()->attach($unlisted_content_element, ['sort_order' => 1, 'unlisted' => true, 'expandable' => false, 'version_id' => $page->published_version_id]);

        $unpublished_content_element = ContentElement::factory()->for(TextBlock::factory(), 'content')->create();

        $page->contentElements()->attach($unpublished_content_element, ['sort_order' => 2, 'unlisted' => false, 'expandable' => false, 'version_id' => $page->draft_version_id]);

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
        $content_element = $this->createContentElement(TextBlock::factory(), $this->getModel());
        $this->assertEquals(1, $content_element->{Str::plural($this->getClassname())}()->count());
        $page = $content_element->{Str::plural($this->getClassname())}()->first();
        $content_element->save();

        $this->assertTrue($page->contentElements->contains('id', $content_element->id));
        $this->assertTrue($page->content_elements->contains('id', $content_element->id));
        $this->assertEquals($page->getDraftVersion()->id, $content_element->contentables->first()->version->id);
        $this->assertEquals(0, $page->pivot->unlisted);

        $page->publish();
        $page->refresh();
        $content_element->refresh();

        $this->assertNotNull($content_element->contentables->first()->version->published_at);
        $this->assertEquals(1, $page->contentElements->count());
        $this->assertTrue(session()->get('editing'));
        $this->assertTrue($page->contentElements->contains('id', $content_element->id));
        $this->assertTrue($page->preview_content_elements->contains('id', $content_element->id));

        $unlisted_content_element = ContentElement::factory()->for(TextBlock::factory(), 'content')->create();

        $page->contentElements()->attach($unlisted_content_element, ['sort_order' => 1, 'unlisted' => true, 'expandable' => false, 'version_id' => $page->published_version_id]);

        $unpublished_content_element = ContentElement::factory()->for(TextBlock::factory(), 'content')->create();

        $page->contentElements()->attach($unpublished_content_element, ['sort_order' => 2, 'unlisted' => false, 'expandable' => false, 'version_id' => $page->draft_version_id]);

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

    /** @test **/
    public function a_page_has_a_resource_attribute()
    {
        $page = $this->getModel();

        $this->assertNotNull($page->resource);
        $this->assertEquals(Str::kebab(Str::plural(class_basename($this->getModel()))), $page->resource);
    }

    /** @test **/
    public function a_page_has_many_page_accesses()
    {
        $page = $this->getModel();
        $role = Role::factory()->create();

        $page->createPageAccess($role);

        $this->assertInstanceOf(Collection::class, $page->pageAccesses()->get());
    }

    /** @test **/
    public function a_page_can_grant_access_to_a_role()
    {
        $page = $this->getModel();
        $role = Role::factory()->create();

        $page->createPageAccess($role);

        $this->assertTrue($role->canEditPage($page));
    }

    /** @test **/
    public function a_page_can_grant_access_to_a_user()
    {
        $page = $this->getModel();
        $user = User::factory()->create();

        $page->createPageAccess($user);

        $this->assertTrue($user->canEditPage($page));
    }

    /** @test **/
    public function a_page_can_check_if_it_is_editable()
    {
        $page = $this->getModel();

        $this->assertNotNull($page->editable);
        $this->assertFalse($page->editable);

        $user1 = User::factory()->create();
        $this->signIn($user1);

        $this->assertFalse($page->editable);

        $role = Role::factory()->create();
        $page->createPageAccess($role);
        $user1->addRole($role);

        $user1->refresh();
        $this->assertTrue($user1->hasRole($role));

        $this->assertTrue($user1->canEditPage($page));
        $this->assertFalse($page->editable);

        session()->put('editing', true);
        $this->assertTrue($page->editable);

        $user2 = User::factory()->create();
        $this->signIn($user2);

        $this->assertFalse($page->editable);
        $page->createPageAccess($user2);
        $user2->refresh();
        $this->assertTrue($page->editable);

        $this->signInAdmin();
        $this->assertTrue($page->editable);
    }
}
