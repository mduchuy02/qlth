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
        Schema::create('lich_hoc', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string("ma_sv", 10);
            $table->unsignedInteger("ma_gd");
            //$table->timestamps();
            $table->primary(['ma_sv', 'ma_gd']);
            $table->foreign("ma_sv")->references("ma_sv")->on("sinh_vien");
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
        Schema::dropIfExists('lich_hoc');
    }
};
