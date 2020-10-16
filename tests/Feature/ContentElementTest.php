<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\ContentElement;
use App\Models\User;
use App\Models\Page;
use App\Models\TextBlock;
use App\Models\Version;

use Illuminate\Support\Facades\Event;
use App\Events\ContentElementSaved;
use App\Events\ContentElementCreated;
use App\Events\ContentElementRemoved;

class ContentElementTest extends TestCase
{
    use WithFaker;

    protected function getModel()
    {
        return factory(ContentElement::class)->states('text-block')->create();
    }
}
