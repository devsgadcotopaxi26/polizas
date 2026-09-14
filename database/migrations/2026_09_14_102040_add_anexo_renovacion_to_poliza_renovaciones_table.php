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
        Schema::table('poliza_renovaciones', function (Blueprint $table) {
            $table->string('anexo_renovacion')->nullable()->after('archivo_renovacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('poliza_renovaciones', function (Blueprint $table) {
            $table->dropColumn('anexo_renovacion');
        });
    }
};
