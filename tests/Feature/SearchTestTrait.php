<?php

namespace Tests\Feature;

use Illuminate\Support\Str;

use App\Models\User;

trait SearchTestTrait
{
    abstract protected function getClassname();

    /** @test **/
    public function an_object_can_be_searched()
    {
        $object = $this->getModel();

        $this->assertNotNull($object->search_label);

        $input = [
            'autocomplete' => true,
            'terms' => $object->search_label,
        ];

        $this->json('POST', route(Str::plural($this->getClassname()).'.search'), $input)
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route(Str::plural($this->getClassname()).'.search'), $input)
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route(Str::plural($this->getClassname()).'.search'), $input)
             ->assertSuccessful()
             ->assertJsonFragment([
                'search_label' => $object->search_label,
             ]);
    }
}
