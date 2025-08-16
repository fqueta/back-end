<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_permission', function (Blueprint $table) {
            $table->id();

            // Relacionamento com perfil (permission)
            $table->unsignedBigInteger('permission_id');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

            // Relacionamento com menu
            $table->unsignedBigInteger('menu_id');
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');

            // Permissões (checkboxes da tela)
            $table->boolean('can_view')->default(0);
            $table->boolean('can_create')->default(0);
            $table->boolean('can_edit')->default(0);
            $table->boolean('can_delete')->default(0);
            $table->boolean('can_upload')->default(0);

            $table->timestamps();

            // Evita duplicidade (mesmo perfil e menu)
            $table->unique(['permission_id', 'menu_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_permission');
    }
};
