<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Cria a tabela 'cursos' para cadastro de cursos.
     */
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome', 300);
            $table->string('titulo', 300)->nullable();
            $table->enum('ativo', ['n','s'])->default('n');
            $table->enum('destaque', ['n','s'])->default('n');
            $table->enum('publicar', ['n','s'])->default('n');
            $table->integer('duracao')->default(0);
            $table->string('unidade_duracao', 20)->nullable();
            $table->string('tipo', 20)->nullable();
            $table->string('categoria', 100)->nullable();
            $table->text('token')->nullable();
            $table->text('autor')->nullable();
            $table->json('config')->nullable();
            $table->decimal('inscricao', 12, 2)->nullable();
            $table->decimal('valor', 12, 2)->nullable();
            $table->integer('parcelas')->default(1);
            $table->decimal('valor_parcela', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Remove a tabela 'cursos'.
     */
    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};