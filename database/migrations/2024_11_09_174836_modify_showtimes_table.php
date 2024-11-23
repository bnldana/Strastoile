<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyShowtimesTable extends Migration
{
    public function up()
    {
        Schema::table('showtimes', function (Blueprint $table) {
            $table->date('day')->after('cinema_id'); // Ajout de la colonne `day`
            $table->json('horaires')->after('day'); // Ajout de la colonne `horaires`
            $table->dropColumn('time'); // Suppression de la colonne `time`
        });
    }

    public function down()
    {
        Schema::table('showtimes', function (Blueprint $table) {
            $table->string('time')->after('cinema_id'); // Réajoute la colonne `time` en cas de rollback
            $table->dropColumn(['day', 'horaires']); // Supprime les colonnes `day` et `horaires`
        });
    }
}
