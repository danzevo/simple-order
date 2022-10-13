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
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->string('document_code', 3)->default('TRX');
            $table->string('document_number', 10);

            $table->string('product_code', 18);
            $table->decimal('price', 10);
            $table->integer('quantity');
            $table->string('unit', 5)->default('PCS');
            $table->decimal('subtotal', 10);
            $table->string('currency', 5)->default('IDR');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_details');
    }
};
