<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyDurationColumnInFilmsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('films', function (Blueprint $table) {
            $table->string('duration', 10)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('films', function (Blueprint $table) {
            // Si vous voulez revenir au type précédent
            $table->time('duration')->nullable()->change();
            // ou
            // $table->string('duration', 5)->nullable()->change();
            // selon votre type initial
        });
    }
}
