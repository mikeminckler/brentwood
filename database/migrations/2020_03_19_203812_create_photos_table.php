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

            $table->unsignedBigInteger('photo_block_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('alt')->nullable();
            $table->integer('sort_order');
            $table->integer('span');
            $table->integer('offsetX');
            $table->integer('offsetY');

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
