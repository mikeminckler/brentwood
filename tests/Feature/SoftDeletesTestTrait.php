<?php

namespace Tests\Feature;

use Illuminate\Support\Str;

use App\Models\User;

trait SoftDeletesTestTrait
{
    abstract protected function getModel();

    /** @test **/
    public function a_model_can_be_soft_deleted()
    {
        $model = $this->getModel();
        $model_class = get_class($model);
        $model_name = Str::title(str_replace('-', ' ', Str::kebab(class_basename($model))));
        $resource = Str::kebab(class_basename($model)).'s';

        $this->json('POST', route($resource.'.remove', ['id' => $model->id]))
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->json('POST', route($resource.'.remove', ['id' => $model->id]))
            ->assertStatus(403);

        $this->assertEquals(1, $model_class::where('id', $model->id)->get()->count());
        $this->signInAdmin();

        $this->json('POST', route($resource.'.remove', ['id' => $model->id]))
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => $model_name.' Removed',
            ]);
        $this->assertEquals(0, $model_class::where('id', $model->id)->get()->count());
    }

    /** @test **/
    public function a_model_can_be_restored()
    {
        $model = $this->getModel();
        $model_class = get_class($model);
        $model_name = Str::title(str_replace('-', ' ', Str::kebab(class_basename($model))));
        $resource = Str::kebab(class_basename($model)).'s';

        $model->delete();
        $this->assertEquals(0, $model_class::where('id', $model->id)->get()->count());

        $this->json('POST', route($resource.'.restore', ['id' => $model->id]))
            ->assertStatus(401);

        $this->signIn(User::factory()->create());

        $this->withoutExceptionHandling();
        $this->json('POST', route($resource.'.restore', ['id' => $model->id]))
            ->assertStatus(403);

        $this->signInAdmin();

        $this->json('POST', route($resource.'.restore', ['id' => $model->id]))
            ->assertSuccessful()
            ->assertJsonFragment([
                'success' => $model_name.' Restored',
            ]);

        $this->assertEquals(1, $model_class::where('id', $model->id)->get()->count());
    }
}
