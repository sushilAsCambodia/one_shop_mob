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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->comment('reference to products table');
            $table->string('slot_price')->default(1);
            $table->string('deal_price')->nullable();
            $table->enum('status', ['active','inactive','settled'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            //$table->index(['product_id']);

        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deals');
    }
};
