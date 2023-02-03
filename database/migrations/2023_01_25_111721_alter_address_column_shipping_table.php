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
        if (Schema::hasTable('shippings')) {
             Schema::table('shippings', function (Blueprint $table) {
                 $table->dropColumn('address');

             });
             Schema::table('shippings', function (Blueprint $table) {
                $table->bigInteger('address_id')->nullable()->after('customer_id')->default(0);
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
        if (Schema::hasTable('shippings')) {
            Schema::table('shippings', function (Blueprint $table) {
                $table->dropColumn('address_id');
                $table->string('address')->after('booking_id');
            });
        }
    }
};
