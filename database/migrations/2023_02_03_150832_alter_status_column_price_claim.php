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
        DB::statement("ALTER TABLE `price_claims` CHANGE `status` `status` ENUM('pending','claimed','canceled','completed','shipping') NULL DEFAULT 'pending' ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `price_claims` CHANGE `status` `status` ENUM('pending','claimed','canceled','completed') NULL DEFAULT 'pending' ");
    }
};
