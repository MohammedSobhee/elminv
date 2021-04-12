<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersSessionDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_session_data', function (Blueprint $table) {
            $table->text('page_list')->after('user_data')->nullable();
            $table->bigInteger('page_last_visited')->after('page_list')->nullable();
            $table->text('parent_page')->after('page_last_visited')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *P
     * @return void
     */
    public function down()
    {
        //
    }
}
