<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_view_other_polizas')->default(false)->after('is_active');
        });

        // Preserva el acceso actual de la cuenta que ya venía viendo todos los tipos de póliza.
        DB::table('users')->where('email', 'paulina.lopez@cotopaxi.gob.ec')
            ->update(['can_view_other_polizas' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('can_view_other_polizas');
        });
    }
};
