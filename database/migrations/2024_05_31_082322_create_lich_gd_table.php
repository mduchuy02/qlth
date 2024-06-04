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
        Schema::create('lich_gd', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments("ma_gd");
            $table->string("ma_gv", 10);
            $table->string("ma_mh", 20);
            $table->string("phong_hoc", 10)->nullable(false);
            $table->string("thoi_gian", 80)->nullable(false);
            $table->integer("st_bd");
            $table->integer("st_kt");
            //$table->timestamps();

            $table->foreign("ma_gv")->references("ma_gv")->on("giao_vien");
            $table->foreign("ma_mh")->references("ma_mh")->on("mon_hoc");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lich_gd');
    }
};
