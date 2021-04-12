<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddress1Address2StateZipcodeToSchoolContactInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_contact_info', function (Blueprint $table) {
            $table->text('address1')->after('extension')->nullable();
            $table->text('address2')->after('address1')->nullable();
            $table->text('city')->after('address2')->nullable();
            $table->text('state')->after('city')->nullable();
            $table->text('zip')->after('state')->nullable();
            $table->text('country')->after('zip')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_contact_info', function (Blueprint $table) {
            //
        });
    }
}
