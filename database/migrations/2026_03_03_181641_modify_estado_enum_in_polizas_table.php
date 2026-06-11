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
        DB::statement("ALTER TABLE polizas MODIFY COLUMN estado ENUM('vigente', 'acta_provisional', 'acta_definitiva', 'liquidada', 'original', 'renovada') DEFAULT 'vigente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE polizas MODIFY COLUMN estado ENUM('vigente', 'acta_provisional', 'acta_definitiva', 'liquidada') DEFAULT 'vigente'");
    }
};
