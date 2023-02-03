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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->foreignId('category_id')->constrained()->nullable();
            $table->foreignId('sub_category_id')->constrained()->nullable();
            $table->string('sku')->comment('Stock-Keeping Unit');
            $table->string('slug')->comment('part of a URL that is easy-to-read');
            $table->integer('quantity');

           // $table->string('sale_price')->nullable();
           // $table->string('purchase_price')->nullable();
            //$table->integer('slots')->nullable();
            $table->text('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->bigInteger('views')->default(0);
            $table->enum('status',['active','inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['slug','deleted_at']);
            $table->unique(['sku','deleted_at']);


            //$table->index(['name', 'category_id','subcategory_id','sku','slug','created_at']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
