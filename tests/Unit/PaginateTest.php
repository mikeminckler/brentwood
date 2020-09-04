<?php

namespace Tests\Unit;

use Tests\TestCase;

use Illuminate\Pagination\LengthAwarePaginator as Paginator;

use App\Blog;
use App\Paginate;

class PaginateTest extends TestCase
{

    /** @test **/
    public function a_paginated_collection_can_be_created()
    {
        $blogs = factory(Blog::class, 3)->create();
        $blogs = Paginate::create($blogs);
        $this->assertInstanceOf(Paginator::class, $blogs);
    }
}
