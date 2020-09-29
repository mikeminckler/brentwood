<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Page;
use App\Models\ContentElement;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function signIn($user = null)
    {
        if (!$user instanceof User) {
            $user = User::factory()->create();
        }

        $this->actingAs($user);
        $this->assertEquals($user->id, auth()->user()->id);

        return $this;
    }

    protected function signInAdmin()
    {
        $user = User::find(1);
        //$user->addRole('admin');
        return $this->signIn($user);
    }

    protected function createContentElement(Factory $factory, $page = null)
    {
        if (!$page) {
            $page = Page::factory()->create();
        }

        $content_element = ContentElement::factory()->for($factory, 'content')->create([
            'version_id' => $page->draft_version_id,
        ]);

        $relationship = Str::plural($page->type);

        $content_element->{$relationship}()->detach();
        $content_element->{$relationship}()->attach($page, ['sort_order' => 1, 'unlisted' => false, 'expandable' => false]);
        return $content_element;
    }

    protected function getFactory()
    {
        $class_name = $this->getClassString();
        return (new $class_name)::factory();
    }

    protected function getModel()
    {
        $class_name = $this->getClassString();
        return (new $class_name)::factory()->create();
    }

    protected function getClassString()
    {
        return 'App\\Models\\'.Str::title($this->getClassname());
    }
}
