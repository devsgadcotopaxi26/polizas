<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('oficios', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero')->comment('Número incremental del oficio, ej: 110');
            $table->unsignedSmallInteger('anio')->comment('Año del oficio, ej: 2026');
            $table->string('codigo_completo', 100)->unique()->comment('Ej: Oficio N° 110-2026-GADPC-T-POLIZA');
            $table->date('fecha_generacion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oficios');
    }
};
