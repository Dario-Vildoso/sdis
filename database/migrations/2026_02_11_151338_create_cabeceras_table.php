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
        Schema::create('cabeceras', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion');
            $table->unsignedBigInteger('variable_id');
            $table->foreign('variable_id')->references('id')->on('variables');
            $table->unsignedBigInteger('cabecera_id')->nullable();
            $table->foreign('cabecera_id')->references('id')->on('cabeceras');
            // $table->integer('orden');
            // $table->boolean('es_hoja');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabeceras');
    }
};
