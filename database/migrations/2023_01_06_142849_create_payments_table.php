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
        ini_set('memory_limit', -1);
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->string('payment_id');
            $table->string('customer_id');
            $table->string('amount');
            $table->string('provider');
            $table->enum('status',['pending','complete'])->default('pending');
            $table->timestamps();
            $table->softDeletes();

            //$table->index(['order_id','payment_id','customer_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
