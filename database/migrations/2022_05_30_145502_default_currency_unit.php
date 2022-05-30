<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DefaultCurrencyUnit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->integer('default_currency')->default(1);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('default_currency');

        });
    }
}
