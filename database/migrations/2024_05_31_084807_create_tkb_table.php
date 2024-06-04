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
        Schema::create('tkb', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments("ma_tkb");
            $table->unsignedInteger("ma_gd");
            $table->date("ngay_hoc")->nullable(false);
            $table->string("phong_hoc", 10)->nullable(false);
            $table->integer("st_bd");
            $table->integer("st_kt");
            //$table->timestamps();

            $table->foreign("ma_gd")->references("ma_gd")->on("lich_gd");
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tkb');
    }
};
