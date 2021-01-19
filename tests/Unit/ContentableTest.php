<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\TextBlock;
use App\Models\Page;
use App\Models\ContentElement;
use App\Models\Contentable;
use App\Models\Version;

class ContentableTest extends TestCase
{

    /** @test **/
    public function a_contentable_has_a_content_element()
    {
        $content_element = $this->createContentElement(TextBlock::factory());
        $contentable = $content_element->contentables->first();
        $this->assertInstanceOf(Contentable::class, $contentable);
        $this->assertInstanceOf(ContentElement::class, $contentable->contentElement);
    }

    /** @test **/
    public function a_contentable_has_a_pageable()
    {
        $content_element = $this->createContentElement(TextBlock::factory());
        $contentable = $content_element->contentables->first();

        $this->assertInstanceOf(Page::class, $contentable->pageable->first());
    }

    /** @test **/
    public function a_contentable_has_a_version()
    {
        $content_element = $this->createContentElement(TextBlock::factory());
        $contentable = $content_element->contentables->first();

        $this->assertInstanceOf(Version::class, $contentable->version);
           
    }

}
