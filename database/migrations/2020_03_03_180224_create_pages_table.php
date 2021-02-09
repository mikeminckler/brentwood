<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->index();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->unsignedBigInteger('parent_page_id');
            $table->unsignedBigInteger('published_version_id')->nullable();
            $table->integer('sort_order');
            $table->boolean('unlisted')->default(false);
            $table->boolean('show_sub_menu')->default(false);
            $table->string('footer_color')->nullable();
            $table->unsignedBigInteger('footer_fg_photo_id')->nullable();
            $table->unsignedBigInteger('footer_bg_photo_id')->nullable();
            $table->dateTime('publish_at')->nullable();
            $table->boolean('protected')->default(false);
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
        Schema::dropIfExists('pages');
    }
}
