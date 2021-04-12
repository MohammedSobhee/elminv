<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideoConsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_cons', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('service', 20);
            $table->tinyInteger('vtype');
            $table->integer('vtype_id');
            $table->string('label')->nullable();
            $table->string('link');
            $table->string('meeting_id', 100)->nullable();
            $table->string('password', 50)->nullable();
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
        Schema::dropIfExists('video_cons');
    }
}
