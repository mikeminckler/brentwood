<?php

namespace Tests\Unit;

use Tests\TestCase;

use Illuminate\Pagination\LengthAwarePaginator as Paginator;

use App\Models\Blog;
use App\Utilities\Paginate;

class PaginateTest extends TestCase
{

    /** @test **/
    public function a_paginated_collection_can_be_created()
    {
        $blogs = Blog::factory()->count(3)->create();
        $paginator = Paginate::create($blogs);
        $this->assertInstanceOf(Paginator::class, $paginator);

        $this->assertTrue($blogs->contains('full_slug', $paginator->random()->full_slug));
    }
}
