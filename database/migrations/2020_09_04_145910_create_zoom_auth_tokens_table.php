<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZoomAuthTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zoom_auth_tokens', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('token_id', 50);
            $table->string('account_id', 50);
            $table->text('access_token');
            $table->text('refresh_token');
            $table->dateTime('expires_at');
            $table->string('personal_url');
            $table->string('avatar');
            $table->string('email');
            $table->string('name');
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
        Schema::dropIfExists('zoom_auth_tokens');
    }
}
