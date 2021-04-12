<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAddress1Address2CityStateZipCountryToBeString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_contact_info', function (Blueprint $table) {
            $table->string('address1')->change();
            $table->string('address2')->change();
            $table->string('city')->change();
            $table->string('state')->change();
            $table->string('zip')->change();
            $table->string('country')->change();
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
