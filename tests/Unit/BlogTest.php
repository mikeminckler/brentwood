<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\Unit\AppendAttributesTestTrait;
use Tests\Unit\ContentElementsTestTrait;
use Tests\Unit\VersioningTestTrait;

use App\Blog;

class BlogTest extends TestCase
{
    use WithFaker;
    use AppendAttributesTestTrait;
    use ContentElementsTestTrait;
    use VersioningTestTrait;

    protected function getModel()
    {
        return factory(Blog::class)->create();
    }

    protected function getClassname()
    {
        return 'blog';
    }

}
