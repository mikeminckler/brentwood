<?php

namespace Tests\Unit;

use App\Tag;

trait TagsTrait
{
    abstract protected function getModel();
    //abstract protected function getClassname();

    /** @test **/
    public function an_object_can_have_many_tags()
    {
        $object = $this->getModel();
        $tag = factory(Tag::class)->create();

        $object->tags()->attach($tag);

        $object->refresh();

        $this->assertEquals(1, $object->tags()->count());
        $this->assertInstanceOf(Tag::class, $object->tags()->first());
        $this->assertEquals($tag->id, $object->tags()->first()->id);
    }

    /** @test **/
    public function a_tag_can_be_added_to_an_object()
    {
        $object = $this->getModel();
        $tag = factory(Tag::class)->create();

        $object->addTag($tag);
        $object->refresh();

        $this->assertEquals(1, $object->tags()->count());
        $this->assertEquals($tag->id, $object->tags()->first()->id);

        $object->addTag($tag);
        $this->assertEquals(1, $object->tags()->count());
    }
}
