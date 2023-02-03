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
        Schema::create('slot_deals', function (Blueprint $table) {
            $table->id();
            $table->string('booking_id')->nullable();
            $table->string('order_id')->constrained()->comment('reference to order table');
            $table->foreignId('deal_id')->constrained()->comment('reference to deals table');
            $table->foreignId('slot_id')->constrained()->comment('reference to slots table');
            $table->string('amount')->nullable();
            $table->enum('status', ['reserved', 'confirmed', 'canceled'])->default('reserved');
            $table->timestamps();
            $table->softDeletes();

            //$table->index(['booking_id','order_id','deal_id','slot_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slot_deals');
    }
};
