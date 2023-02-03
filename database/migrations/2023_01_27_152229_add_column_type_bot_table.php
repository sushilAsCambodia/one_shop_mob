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
        if (Schema::hasTable('configures')) {
            Schema::table('configures', function (Blueprint $table) {
                $table->enum('type',['bot','mlm'])->nullable()->after('data');
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
        if (Schema::hasTable('configures')) {
            Schema::table('configures', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
