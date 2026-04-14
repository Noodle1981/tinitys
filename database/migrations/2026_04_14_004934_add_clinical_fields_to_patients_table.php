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
        Schema::table('patients', function (Blueprint $table) {
            $table->string('dni')->unique()->nullable()->after('id');
            $table->date('birth_date')->nullable()->after('name');
            $table->string('gender')->nullable()->after('birth_date');
            $table->string('occupation')->nullable()->after('gender');
            
            // Tinnitus Context
            $table->string('laterality')->nullable(); // Derecho, Izquierdo, Bilateral
            $table->string('sound_type')->nullable(); // Pitido, siseo, etc.
            $table->string('evolution_time')->nullable(); // Agudo/Crónico
            
            // Medical History
            $table->json('comorbidities')->nullable(); // Hipoacusia, Vértigo, etc.
            $table->text('mental_health_context')->nullable();
            $table->text('medications')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'dni', 'birth_date', 'gender', 'occupation',
                'laterality', 'sound_type', 'evolution_time',
                'comorbidities', 'mental_health_context', 'medications'
            ]);
        });
    }
};
