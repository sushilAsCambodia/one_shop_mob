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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('addressable_id');
            $table->string('addressable_type');
            $table->foreignId('country_id')->constrained()->comment('reference to countries table');
            $table->foreignId('state_id')->nullable()->constrained()->comment('reference to states table');
            $table->foreignId('city_id')->nullable()->constrained()->comment('reference to cities table');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('street_address_1');
            $table->string('street_address_2')->nullable();
            $table->integer('pincode');
            $table->string('phone');
            $table->string('email');
            $table->enum('type',['billing','shipping']);
            $table->timestamps();
            $table->softDeletes();

            //$table->index(['addressable_id','addressable_type','country_id',
                        //     'state_id','city_id','firstname','lastname','street_address_1',
                        //     'street_address_2','pincode','phone','email'
                        // ]);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
};
