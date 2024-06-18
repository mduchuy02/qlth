<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {

            $table->foreign('ma_sv')->references('ma_sv')->on('sinh_vien')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('ma_gv')->references('ma_gv')->on('giao_vien')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['ma_sv']);
            $table->dropForeign(['ma_gv']);
        });
    }
};
