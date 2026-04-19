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
        Schema::table('patient_habits', function (Blueprint $table) {
            $table->dropColumn('recreational_risks');
            
            $table->boolean('risk_discotecas')->default(false);
            $table->boolean('risk_musica_fuerte')->default(false);
            $table->boolean('risk_caza')->default(false);
            $table->boolean('risk_tiro')->default(false);
            $table->boolean('risk_militar')->default(false);
            $table->boolean('risk_motociclismo')->default(false);
            $table->boolean('risk_automovilismo')->default(false);
            $table->boolean('risk_submarinismo')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_habits', function (Blueprint $table) {
            $table->json('recreational_risks')->nullable();
            
            $table->dropColumn([
                'risk_discotecas', 'risk_musica_fuerte', 'risk_caza', 'risk_tiro', 
                'risk_militar', 'risk_motociclismo', 'risk_automovilismo', 'risk_submarinismo'
            ]);
        });
    }
};
