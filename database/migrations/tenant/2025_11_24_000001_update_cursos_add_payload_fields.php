<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Adiciona campos para suportar payloads avançados (aeronaves, modulos, campo_id, campo_bus).
     */
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->string('campo_id', 50)->nullable()->after('categoria');
            $table->string('campo_bus', 100)->nullable()->after('campo_id');
            $table->json('aeronaves')->nullable()->after('campo_bus');
            $table->json('modulos')->nullable()->after('aeronaves');
        });
    }

    /**
     * Remove os campos adicionados caso seja necessário fazer rollback.
     */
    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn(['campo_id', 'campo_bus', 'aeronaves', 'modulos']);
        });
    }
};