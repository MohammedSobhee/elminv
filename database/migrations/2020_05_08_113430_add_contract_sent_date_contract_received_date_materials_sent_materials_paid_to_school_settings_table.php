<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContractSentDateContractReceivedDateMaterialsSentMaterialsPaidToSchoolSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dateTime('contract_sent_date')->after('contract_expiration_date')->nullable();
            $table->dateTime('contract_received_date')->after('contract_sent_date')->nullable();
            $table->tinyInteger('materials_sent')->after('contract_received_date')->nullable();
            $table->tinyInteger('materials_paid')->after('materials_sent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_settings', function (Blueprint $table) {
            //
        });
    }
}
