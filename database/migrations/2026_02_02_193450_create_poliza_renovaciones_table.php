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
        Schema::create('poliza_renovaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poliza_original_id')->constrained('polizas')->onDelete('cascade');
            $table->foreignId('poliza_nueva_id')->constrained('polizas')->onDelete('cascade');
            $table->date('fecha_renovacion');
            $table->enum('tipo_renovacion', ['manual', 'automatica'])->default('manual');
            $table->text('observaciones')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Índices
            $table->index('poliza_original_id');
            $table->index('poliza_nueva_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poliza_renovaciones');
    }
};
