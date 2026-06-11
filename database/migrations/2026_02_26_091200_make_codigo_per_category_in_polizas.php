<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Drop unique index on codigo (codes will repeat across categories)
        Schema::table('polizas', function (Blueprint $table) {
            $table->dropUnique(['codigo']);
        });

        // Add composite unique: codigo + categoria_poliza
        Schema::table('polizas', function (Blueprint $table) {
            $table->unique(['codigo', 'categoria_poliza']);
        });

        // Re-assign sequential codes per category
        $categories = DB::table('polizas')->distinct()->pluck('categoria_poliza');
        foreach ($categories as $cat) {
            $polizas = DB::table('polizas')
                ->where('categoria_poliza', $cat)
                ->orderBy('id')
                ->get();
            $codigo = 1;
            foreach ($polizas as $poliza) {
                DB::table('polizas')->where('id', $poliza->id)->update(['codigo' => $codigo++]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->dropUnique(['codigo', 'categoria_poliza']);
            $table->unique('codigo');
        });
    }
};
