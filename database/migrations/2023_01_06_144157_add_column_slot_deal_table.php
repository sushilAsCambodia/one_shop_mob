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
        if (Schema::hasTable('slot_deals')) {
            Schema::table('slot_deals', function (Blueprint $table) {
                $table->boolean('is_bot')->default(false)->after('amount');
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
        if (Schema::hasTable('slot_deals')) {
            Schema::table('slot_deals', function (Blueprint $table) {
                $table->dropColumn('is_bot');
            });
        }
    }
};
