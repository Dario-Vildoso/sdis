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
        Schema::table('formularios', function (Blueprint $table) {
            $table->string('descripcion')->nullable()->after('id');
        });
        Schema::table('establecimientos', function (Blueprint $table) {
            $table->dropColumn('gestion');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formularios', function (Blueprint $table) {
            $table->dropColumn('descripcion');
        });
        Schema::table('establecimientos', function (Blueprint $table) {
            $table->string('gestion')->nullable()->after('nombre');
        });
    }
};
