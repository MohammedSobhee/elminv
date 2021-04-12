<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentDuePaymentReceivedDateAutoRenewalSentAutoRenewalReceivedCertifiedCertifiedDateContestToSchoolSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->tinyInteger('payment_due')->after('materials_paid')->nullable();
            $table->dateTime('payment_received_date')->after('payment_due')->nullable();
            $table->tinyInteger('auto_renewal_sent')->after('payment_received_date')->nullable();
            $table->tinyInteger('auto_renewal_received')->after('auto_renewal_sent')->nullable();
            $table->string('certified', 100)->after('auto_renewal_received')->nullable();
            $table->dateTime('certified_date')->after('certified')->nullable();
            $table->string('contest', 50)->after('certified_date')->nullable();
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
