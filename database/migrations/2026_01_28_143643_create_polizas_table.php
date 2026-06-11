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
        Schema::create('polizas', function (Blueprint $table) {
            $table->id();

            // Datos básicos de la póliza
            $table->string('numero_poliza')->unique();
            $table->string('tipo_seguro'); // Vida, Auto, Incendio, etc.
            $table->decimal('valor_asegurado', 15, 2);
            $table->decimal('prima', 15, 2)->nullable();

            // Fechas
            $table->date('fecha_inicio');
            $table->date('fecha_vencimiento');

            // Beneficiario
            $table->string('beneficiario_nombre');
            $table->string('beneficiario_email');
            $table->string('beneficiario_telefono')->nullable();
            $table->string('beneficiario_cedula')->nullable();

            // Relaciones
            $table->foreignId('aseguradora_id')->constrained('aseguradoras')->onDelete('restrict');
            $table->foreignId('direccion_id')->constrained('direcciones')->onDelete('restrict');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');

            // Estado y observaciones
            $table->enum('estado', ['activa', 'vencida', 'renovada', 'cancelada'])->default('activa');
            $table->text('observaciones')->nullable();
            $table->text('documentos_adjuntos')->nullable(); // JSON de rutas de archivos

            // Campos de auditoría
            $table->boolean('notificacion_enviada')->default(false);
            $table->timestamp('fecha_ultima_notificacion')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polizas');
    }
};
