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
        Schema::create('shippings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('shipping_id')->nullable()->comment('reference to shipping table');
            $table->string('booking_id')->references('booking_id')->on('slot_deals')->comment('reference to slot Deals table');
            $table->bigInteger('carrier_id')->comment('reference to  table');
            $table->string('address')->comment('reference to address table');
            $table->string('tracking_id')->comment('reference to shipping table');
            $table->foreignId('customer_id')->comment('reference to customer table');
            $table->enum('status', ['Pending', 'In Transit', 'Delivered', 'Returned To Seller', 'Failed Delivery', 'Cancelled'])->default('Pending');
            $table->timestamps();
            $table->softDeletes();

            //$table->index(['customer_id','booking_id','shipping_id','carrier_id','address','tracking_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping');
    }
};
