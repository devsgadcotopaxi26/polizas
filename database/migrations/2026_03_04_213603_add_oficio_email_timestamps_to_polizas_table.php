<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->timestamp('oficio_email_1_at')->nullable()->after('oficio_firmado_tesorero');
            $table->timestamp('oficio_email_2_at')->nullable()->after('oficio_email_1_at');
        });
    }

    public function down(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->dropColumn(['oficio_email_1_at', 'oficio_email_2_at']);
        });
    }
};
