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
        Schema::table('tinnitus_profiles', function (Blueprint $table) {
            // Qué oído/s tiene tinnitus en esta sesión
            $table->string('affected_ears')->default('ambos')->after('initiated_by'); // 'OI', 'OD', 'ambos'

            // Frecuencia percibida por oído (el global existente queda por compatibilidad)
            $table->string('left_freq_selected')->nullable()->after('frequency_perception');
            $table->string('right_freq_selected')->nullable()->after('left_freq_selected');

            // Índice de confiabilidad por oído
            $table->integer('left_index')->nullable()->after('reliability_index');
            $table->integer('right_index')->nullable()->after('left_index');
        });
    }

    public function down(): void
    {
        Schema::table('tinnitus_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'affected_ears',
                'left_freq_selected',
                'right_freq_selected',
                'left_index',
                'right_index',
            ]);
        });
    }
};
