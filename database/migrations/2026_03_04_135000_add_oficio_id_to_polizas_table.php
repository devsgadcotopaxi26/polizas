<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->foreignId('oficio_id')
                ->nullable()
                ->after('oficio_path')
                ->constrained('oficios')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->dropForeign(['oficio_id']);
            $table->dropColumn('oficio_id');
        });
    }
};
