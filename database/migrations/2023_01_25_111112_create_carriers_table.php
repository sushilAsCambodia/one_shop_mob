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
        Schema::create('carriers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('reference to carriers table');
            $table->string('email')->comment('reference to carriers table');
            $table->string('contact_no')->comment('reference to carriers table');
            $table->string('website')->comment('reference to carriers table');
            $table->string('tracking_url')->comment('reference to carriers table');
            $table->string('address')->comment('reference to carriers table');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carriers');
    }
};
