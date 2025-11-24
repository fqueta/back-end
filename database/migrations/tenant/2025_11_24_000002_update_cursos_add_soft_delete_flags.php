<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Adiciona colunas para marcação de exclusão (lixeira) em cursos.
     */
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->enum('excluido', ['n','s'])->default('n')->after('valor_parcela');
            $table->enum('deletado', ['n','s'])->default('n')->after('excluido');
            $table->text('excluido_por')->nullable()->after('deletado');
            $table->text('deletado_por')->nullable()->after('excluido_por');
            $table->json('reg_excluido')->nullable()->after('deletado_por');
            $table->json('reg_deletado')->nullable()->after('reg_excluido');
        });
    }

    /**
     * Remove as colunas de lixeira em rollback.
     */
    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn([
                'excluido',
                'deletado',
                'excluido_por',
                'deletado_por',
                'reg_excluido',
                'reg_deletado',
            ]);
        });
    }
};