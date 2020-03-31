<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentElementPageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_element_page', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('content_element_id');
            $table->unsignedBigInteger('page_id');
            $table->integer('sort_order');
            $table->boolean('unlisted');
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
        Schema::dropIfExists('content_element_page');
    }
}
