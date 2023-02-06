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
        Schema::create('order_deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->comment('reference to customer table');
            $table->foreignId('order_id')->nullable()->constrained('orders')->comment('reference to orders table');
            $table->foreignId('deal_id')->nullable()->constrained('deals')->comment('reference to deals table');
            $table->string('amount')->comment('reference to order table');
            $table->string('slots')->comment('slot amount');
            $table->string('quantity')->nullable()->comment('reference to order table');
            $table->enum('status', ['winner','reserved','confirmed','canceled','loser','completed','shipping'])->default('reserved');
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
        Schema::dropIfExists('order_deals');
    }
};
