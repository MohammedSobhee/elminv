<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoursePagesToAssignmentClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assignment_classes', function (Blueprint $table) {
            $table->json('course_pages')->after('assignment_id')->nullable();
            $table->tinyInteger('insert_status')->after('course_pages')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assignment_classes', function (Blueprint $table) {
            //
        });
    }
}
