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
        Schema::table('service_orders', function (Blueprint $table) {
            // Tornar o campo object_id nullable para permitir ordens de serviço sem objeto específico
            $table->unsignedBigInteger('object_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            // Reverter o campo object_id para não nullable
            $table->unsignedBigInteger('object_id')->nullable(false)->change();
        });
    }
};
