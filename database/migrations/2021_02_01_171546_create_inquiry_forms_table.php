<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInquiryFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inquiry_forms', function (Blueprint $table) {
            $table->id();
            $table->string('header')->nullable();
            $table->mediumText('body')->nullable();
            $table->boolean('show_student_info')->default(false);
            $table->boolean('show_interests')->default(false);
            $table->boolean('show_livestreams')->default(false);
            $table->boolean('show_livestreams_first')->default(false);
            $table->boolean('create_password')->default(false);
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
        Schema::dropIfExists('inquiry_forms');
    }
}
