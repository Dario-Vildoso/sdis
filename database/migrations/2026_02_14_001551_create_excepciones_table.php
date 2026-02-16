<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('excepcions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subvariable_id');
            $table->foreign('subvariable_id')->references('id')->on('sub_variables');
            $table->unsignedBigInteger('cabecera_id');
            $table->foreign('cabecera_id')->references('id')->on('cabeceras');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excepciones');
    }
};
