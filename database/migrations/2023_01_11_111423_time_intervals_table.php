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
        Schema::create('time_intervals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained('deals')->comment('Ref on document_types');
            $table->dateTime('interval_time')->nullable()->default(null);
            $table->enum('status',['pending','done']);
            $table->timestamps();

            //$table->index(['deal_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('time_intervals');
    }
};
