<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contratistas', function (Blueprint $table) {
            $table->dropColumn('num_cedula');
        });
    }

    public function down(): void
    {
        Schema::table('contratistas', function (Blueprint $table) {
            $table->string('num_cedula', 10)->nullable()->unique()->after('celular_cont');
        });
    }
};
