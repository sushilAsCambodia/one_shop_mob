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
        Schema::create('add_to_carts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->constrained('customers');
            $table->string('p_id');
            $table->string('p_name');
            $table->string('quantity');
            $table->string('price');
            $table->string('image');

            $table->timestamps();

            //$table->index(['customer_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('add_to_carts');
    }
};
