<?php

namespace Tests\Unit;

use Illuminate\Support\Arr;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

use App\Events\PageDraftCreated;
use App\Events\BlogDraftCreated;

use App\Models\Version;
use App\Models\Page;
use App\Models\User;
use App\Models\ContentElement;
use App\Models\TextBlock;

trait VersioningTestTrait
{
    abstract protected function getModel();
    abstract protected function getClassname();

    /** @test **/
    public function a_page_can_get_its_draft_version()
    {
        $page = $this->getModel();

        $this->assertInstanceOf(get_class($this->getModel()), $page);
        $draft_version = $page->getDraftVersion();
        $this->assertInstanceOf(Version::class, $draft_version);
    }

    /** @test **/
    public function a_page_has_a_published_version()
    {
        $page = $this->getModel();
        $page->publish();
        $page->refresh();
        $this->assertNotNull($page->published_version_id);
        $this->assertNotNull($page->publishedVersion);
        $this->assertInstanceOf(Version::class, $page->publishedVersion);
    }

    /** @test **/
    public function a_page_can_be_published()
    {
        $page = $this->getModel();
        $page->publish();
        $this->assertNotNull($page->published_version_id);
        $this->assertNotNull($page->publishedVersion);
        $this->assertInstanceOf(Version::class, $page->publishedVersion);
        $this->assertNotNull($page->publishedVersion->published_at);
        $this->assertNotNull($page->published_at);
    }

    /** @test **/
    public function a_page_has_many_versions()
    {
        $page = $this->getModel();
        $version = Version::factory()->create([
            'versionable_type' => get_class($page),
            'versionable_id' => $page->id,
        ]);
        $version->versionable_id = $page->id;
        $version->save();
        $page->refresh();
        $this->assertTrue($page->versions()->get()->contains('id', $version->id));
    }

    /** @test **/
    public function if_a_page_doesnt_have_a_draft_version_one_is_created()
    {
        $page = $this->getModel();
        $this->assertInstanceOf(Version::class, $page->getDraftVersion());
    }

    /** @test **/
    public function a_page_has_a_draft_version_id_attribute()
    {
        $page = $this->getModel();
        $this->assertNotNull($page->draft_version_id);
        $this->assertEquals($page->getDraftVersion()->id, $page->draft_version_id);
    }
    
    /** @test **/
    public function a_page_can_be_published_in_the_future()
    {
        $class_name = get_class($this->getModel());
        $page = (new $class_name)::factory()->unpublished()->create([
            'publish_at' => now()->addMinutes(1),
        ]);

        $this->assertInstanceOf(Version::class, $page->getDraftVersion());
        $this->assertNull($page->published_at);

        Page::publishScheduledContent();
        $page->refresh();
        $this->assertNull($page->published_at);

        $page->publish_at = now()->subMinutes(1);
        $page->save();
        $page->refresh();
        $this->assertTrue($page->publish_at->isPast());

        Artisan::call('brentwood:publish-scheduled-content');
        $page->refresh();
        $this->assertNotNull($page->published_at);
        $this->assertNull($page->publish_at);
    }

    /** @test **/
    public function creating_a_new_version_broadcasts_an_event()
    {
        $page = $this->getModel();
        $page->publish();

        Event::fake();

        $page->getDraftVersion();

        if ($this->getClassname() === 'page') {
            Event::assertDispatched(function (PageDraftCreated $event) use ($page) {
                return $event->{$this->getClassname()}->id === $page->id;
            });
        } elseif ($this->getClassname() === 'blog') {
            Event::assertDispatched(function (BlogDraftCreated $event) use ($page) {
                return $event->{$this->getClassname()}->id === $page->id;
            });
        }
    }

    /** @test **/
    public function a_page_has_a_can_be_published_attribute()
    {
        $content_element = $this->createContentElement(TextBlock::factory(), $this->getModel());
        $page = $content_element->{Str::plural($this->getClassname())}()->first();
        //$content_element->version_id = $page->draft_version_id;

        $page->refresh();

        $this->assertFalse($page->can_be_published);
        $user = User::factory()->create();
        $this->signIn($user);
        $this->assertFalse($page->can_be_published);

        $user->addRole('publisher');
        $user->refresh();
        $page->refresh();

        $this->assertTrue($page->can_be_published);
        $page->publish();
        $page->refresh();
        $this->assertFalse($page->can_be_published);

        $content_element = ContentElement::factory()->for(TextBlock::factory(), 'content')->create();

        $content_element->{Str::plural($this->getClassname())}()->detach();
        $content_element->{Str::plural($this->getClassname())}()->attach($page, ['sort_order' => 1, 'unlisted' => true, 'expandable' => false, 'version_id' => $page->draft_version_id]);

        $content_element->save();
        
        $page->refresh();
        $this->assertTrue($page->can_be_published);
    }
}
