<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->unsignedBigInteger('codigo')->nullable()->unique()->after('id');
        });

        // Assign sequential codigo to existing records based on id order
        $polizas = DB::table('polizas')->orderBy('id')->get();
        $codigo = 1;
        foreach ($polizas as $poliza) {
            DB::table('polizas')->where('id', $poliza->id)->update(['codigo' => $codigo++]);
        }
    }

    public function down(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->dropColumn('codigo');
        });
    }
};
