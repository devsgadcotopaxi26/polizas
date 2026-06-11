<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('poliza_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poliza_id')->constrained('polizas')->onDelete('cascade');
            $table->string('accion'); // 'creada', 'actualizada', 'estado_cambiado', 'renovada', 'eliminada'
            $table->string('campo_modificado')->nullable(); // nombre del campo que cambió
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();
            $table->text('descripcion')->nullable(); // descripción del cambio
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Índices para mejorar búsquedas
            $table->index(['poliza_id', 'created_at']);
            $table->index('accion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poliza_historial');
    }
};
