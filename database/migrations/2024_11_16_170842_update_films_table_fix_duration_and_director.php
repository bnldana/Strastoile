<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('films', function (Blueprint $table) {
            $table->integer('duration')->nullable()->change();
            $table->string('director')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('films', function (Blueprint $table) {
            $table->string('duration')->nullable(false)->change();
            $table->string('director')->nullable(false)->change();
        });
    }
};
