<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('establecimiento_formularios', function (Blueprint $table) {
            $table->dropForeign(['establecimiento_id']);

            $table->foreign('establecimiento_id')
                ->references('id')
                ->on('establecimientos')
                ->cascadeOnDelete();

            $table->dropForeign(['formulario_id']);

            $table->foreign('formulario_id')
                ->references('id')
                ->on('formularios')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('establecimiento_formularios', function (Blueprint $table) {
            $table->dropForeign(['establecimiento_id']);

            $table->foreign('establecimiento_id')
                ->references('id')
                ->on('establecimientos');

            $table->dropForeign(['formulario_id']);

            $table->foreign('formulario_id')
                ->references('id')
                ->on('formularios');
        });
    }
};
