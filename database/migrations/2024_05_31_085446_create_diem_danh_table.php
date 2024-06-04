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
        Schema::create('diem_danh', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments("ma_dd");
            $table->unsignedInteger("ma_tkb");
            $table->date("ngay_hoc")->nullable(false);
            $table->date("diem_danh1");
            $table->date("diem_danh2");
            //$table->timestamps();

            $table->foreign("ma_tkb")->references("ma_tkb")->on("tkb");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('diem_danh');
    }
};
