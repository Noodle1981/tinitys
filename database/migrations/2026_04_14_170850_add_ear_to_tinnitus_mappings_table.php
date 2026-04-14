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
        Schema::table('tinnitus_mappings', function (Blueprint $table) {
            // Oído/s mapeado/s en esta sesión
            $table->string('ear')->default('ambos')->after('initiated_by'); // 'OI', 'OD', 'ambos'

            // Renombrar layers_config → left_layers_config
            $table->renameColumn('layers_config', 'left_layers_config');

            // Config de capas para el oído derecho (nullable cuando ear = 'OI')
            $table->json('right_layers_config')->nullable()->after('left_layers_config');
        });
    }

    public function down(): void
    {
        Schema::table('tinnitus_mappings', function (Blueprint $table) {
            $table->renameColumn('left_layers_config', 'layers_config');
            $table->dropColumn(['ear', 'right_layers_config']);
        });
    }
};
