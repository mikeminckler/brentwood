<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYoutubeVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('youtube_videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('video_id')->nullable();
            $table->string('title')->nullable();
            $table->boolean('full_width');
            $table->string('header')->nullable();
            $table->mediumText('body')->nullable();
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
        Schema::dropIfExists('youtube_videos');
    }
}
