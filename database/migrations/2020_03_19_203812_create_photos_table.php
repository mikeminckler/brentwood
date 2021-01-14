<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('file_upload_id');
            $table->morphs('content');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('alt')->nullable();
            $table->integer('sort_order');
            $table->integer('span');
            $table->integer('offsetX');
            $table->integer('offsetY');
            $table->boolean('fill');
            $table->string('stat_number')->nullable();
            $table->string('stat_name')->nullable();
            $table->string('link')->nullable();

            $table->string('small')->nullable();
            $table->string('medium')->nullable();
            $table->string('large')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('photos');
    }
}
