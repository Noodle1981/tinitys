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
            $table->string('civil_status')->nullable();
            $table->boolean('has_children')->default(false);
            $table->string('work_status')->nullable();
            $table->integer('work_hours')->nullable();
            $table->string('other_disabilities')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'civil_status',
                'has_children',
                'work_status',
                'work_hours',
                'other_disabilities'
            ]);
        });
    }
};
