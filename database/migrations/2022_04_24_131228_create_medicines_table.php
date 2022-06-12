<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedicinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shelf_id')->nullable();
            $table->foreignId('company_id')->nullable();
            $table->foreignId('material_id')->nullable();
            $table->integer('alternative_id')->nullable();
            $table->string('name');
            $table->integer('quantity');
            $table->integer('pills');
            $table->date('expiration_date');
            $table->integer('c_price');
            $table->integer('price');
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
        Schema::dropIfExists('medicines');
    }
}
