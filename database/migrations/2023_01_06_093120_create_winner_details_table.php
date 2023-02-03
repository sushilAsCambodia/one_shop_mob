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
        Schema::create('winner_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->comment('reference to customer table');
            $table->string('booking_id')->nullable()->comment('reference to slot deals ');
            $table->string('order_id')->nullable()->comment('reference to order table');
            $table->foreignId('deal_id')->nullable()->constrained('deals')->comment('reference to deals table');
            $table->foreignId('slot_id')->nullable()->constrained('slots')->comment('reference to slots table');
            $table->timestamps();
            $table->softDeletes();

            //$table->index(['customer_id','booking_id','order_id','deal_id','slot_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('winner_details');
    }
};
