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
            $table->integer('config_version')->default(1)->after('master_volume');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tinnitus_mappings', function (Blueprint $table) {
            $table->dropColumn('config_version');
        });
    }
};
