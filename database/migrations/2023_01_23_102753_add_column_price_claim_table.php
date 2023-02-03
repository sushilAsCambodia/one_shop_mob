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
        if (Schema::hasTable('price_claims')) {
            Schema::table('price_claims', function (Blueprint $table) {
                $table->bigInteger('address_id')->nullable()->after('customer_id');
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
        if (Schema::hasTable('price_claims')) {
            Schema::table('price_claims', function (Blueprint $table) {
                $table->dropColumn('address_id');
            });
        }
    }
};
