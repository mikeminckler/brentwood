<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contentables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('content_element_id');
            $table->morphs('contentable');
            $table->unsignedBigInteger('version_id');

            $table->integer('sort_order');
            $table->boolean('unlisted');
            $table->boolean('expandable');

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
        Schema::dropIfExists('contentables');
    }
}
