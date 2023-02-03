<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('slots')) {
            Schema::table('slots', function (Blueprint $table) {
                $table->enum('status',['active','settled'])->default('active')->after('booked_slots');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('slots')) {
            Schema::table('slots', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
