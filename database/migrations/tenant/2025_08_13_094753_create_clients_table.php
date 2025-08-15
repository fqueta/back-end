<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255); // Nome Completo
            $table->string('email', 255)->unique(); // Email único
            $table->string('phone', 50)->nullable(); // Telefone
            $table->string('document', 20)->nullable(); // CPF/CNPJ
            $table->string('zip_code', 10)->nullable(); // CEP
            $table->string('address', 255)->nullable(); // Endereço
            $table->json('meta')->nullable(); // meta informações adicionais
            $table->string('city', 100)->nullable(); // Cidade
            $table->string('state', 2)->nullable(); // Estado (sigla)
            $table->boolean('active')->default(true); // Cliente ativo
            $table->timestamps(); // created_at e updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
