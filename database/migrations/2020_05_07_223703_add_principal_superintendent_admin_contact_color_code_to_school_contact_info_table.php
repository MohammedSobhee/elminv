<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrincipalSuperintendentAdminContactColorCodeToSchoolContactInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_contact_info', function (Blueprint $table) {
            $table->string('principal', 100)->after('country')->nullable();
            $table->string('superintendent', 100)->after('principal')->nullable();
            $table->string('admin_contact')->after('superintendent')->nullable();
            $table->tinyInteger('color_code')->after('admin_contact')->nullable();
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
