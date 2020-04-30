<?php

namespace Tests\Unit;
use Illuminate\Support\Arr;

trait AppendAttributesTestTrait
{

    protected abstract function getModel();

    /** @test **/
    public function attributes_can_be_appended_to_a_model()
    {
        $model = $this->getModel();
        $this->assertNotNull($model->append_attributes);

        $model_array = $model->toArray();
        $this->assertNull(Arr::get($model_array, $model->append_attributes[0]));

        $model->appendAttributes($model->append_attributes[0]);
        $model_array = $model->toArray();

        $this->assertNotNull(Arr::get($model_array, $model->append_attributes[0]));
    }

}
