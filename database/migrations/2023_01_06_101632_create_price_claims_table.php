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
        Schema::create('price_claims', function (Blueprint $table) {
            $table->id();
            $table->string('booking_id')->references('booking_id')->on('slot_deals')->comment('reference to slot deals table');
            $table->string('order_id')->comment('reference to order table');
            $table->foreignId('deal_id')->comment('reference to deals table');
            $table->foreignId('product_id')->comment('reference to products table');
            $table->foreignId('customer_id')->comment('reference to customer table');
            $table->enum('status', ['pending', 'claimed', 'canceled'])->default('pending');
            $table->timestamps();
            $table->softDeletes();

            //$table->index(['customer_id','booking_id','order_id','deal_id','product_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_claims');
    }
};
