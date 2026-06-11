<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->dropColumn(['acta_provisional', 'acta_definitiva', 'oficios_renovacion']);
        });

        // Update estado enum: activa -> vigente, vencida/renovada/cancelada -> liquidada
        DB::statement("ALTER TABLE polizas MODIFY COLUMN estado ENUM('vigente', 'acta_provisional', 'acta_definitiva', 'liquidada') DEFAULT 'vigente'");

        // Migrate existing data
        DB::table('polizas')->where('estado', 'activa')->update(['estado' => 'vigente']);
        DB::table('polizas')->whereIn('estado', ['vencida', 'renovada', 'cancelada'])->update(['estado' => 'liquidada']);
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE polizas MODIFY COLUMN estado ENUM('activa', 'vencida', 'renovada', 'cancelada') DEFAULT 'activa'");

        Schema::table('polizas', function (Blueprint $table) {
            $table->string('acta_provisional', 100)->nullable()->after('observaciones');
            $table->string('acta_definitiva', 100)->nullable()->after('acta_provisional');
            $table->text('oficios_renovacion')->nullable()->after('acta_definitiva');
        });
    }
};
