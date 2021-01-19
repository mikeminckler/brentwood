<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentElementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_elements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->morphs('content');
            //$table->unsignedBigInteger('version_id');
            $table->dateTime('publish_at')->nullable();
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
        Schema::dropIfExists('content_elements');
    }
}
