<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhotoBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photo_blocks', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('columns');
            $table->integer('height');
            $table->boolean('padding');
            $table->boolean('show_text');
            $table->string('header')->nullable();
            $table->mediumText('body')->nullable();
            $table->integer('text_order')->nullable();
            $table->integer('text_span')->nullable();

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
        Schema::dropIfExists('photo_blocks');
    }
}
