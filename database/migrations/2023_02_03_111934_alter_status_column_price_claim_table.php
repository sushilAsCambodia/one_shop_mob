<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `price_claims` CHANGE `status` `status` ENUM('pending','claimed','canceled','completed') NULL DEFAULT 'pending' ");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `price_claims` CHANGE `status` `status` ENUM('pending','claimed','canceled') NULL DEFAULT 'pending' ");
    }
};
