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
        if (Schema::hasTable('deals')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->foreignId('slot_id')->constrained('slots')->comment('reference to Slot table');
                $table->bigInteger('time_period')->default(25);
                $table->dateTime('deal_end_at')->nullable()->default(null);
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
        if (Schema::hasTable('deals')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->dropForeign(['slot_id']);
                $table->dropColumn('slot_id');
                $table->dropColumn('time_period');
                $table->dropColumn('deal_end_at');
            });
        }
    }
};
