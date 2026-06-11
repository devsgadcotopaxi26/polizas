<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_contrato', 100)->unique();
            $table->text('objeto_contratacion')->nullable();
            $table->decimal('valor_contrato', 15, 2)->nullable();
            $table->decimal('valor_anticipo', 15, 2)->nullable();
            $table->foreignId('contratista_id')->constrained('contratistas')->onDelete('restrict');
            $table->foreignId('administrador_id')->nullable()->constrained('administradores')->onDelete('set null');
            $table->foreignId('direccion_id')->nullable()->constrained('direcciones')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
