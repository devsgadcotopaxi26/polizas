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
        Schema::table('poliza_renovaciones', function (Blueprint $table) {
            $table->string('archivo_renovacion')->nullable();
            $table->boolean('estado_firma_asesor')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('poliza_renovaciones', function (Blueprint $table) {
            $table->dropColumn(['archivo_renovacion', 'estado_firma_asesor']);
        });
    }
};
