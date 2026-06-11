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
        Schema::table('polizas', function (Blueprint $table) {
            $table->string('oficio_path')->nullable()->after('estado');
            $table->boolean('oficio_firmado_gestor')->default(false)->after('oficio_path');
            $table->boolean('oficio_firmado_tesorero')->default(false)->after('oficio_firmado_gestor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->dropColumn(['oficio_path', 'oficio_firmado_gestor', 'oficio_firmado_tesorero']);
        });
    }
};
