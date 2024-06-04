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
        Schema::create('ket_qua', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments("ma_kq");
            $table->string("ma_sv", 10);
            $table->string("ma_mh", 20);
            $table->decimal("diem_qt");
            $table->decimal("diem_thi1");
            $table->decimal("diem_thi2");
            $table->decimal("diem_tb");
            //$table->timestamps();

            $table->foreign("ma_sv")->references("ma_sv")->on("sinh_vien");
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
        Schema::dropIfExists('ket_qua');
    }
};
