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
        Schema::create('sinh_vien', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string("ma_sv", 10);
            $table->primary("ma_sv");
            $table->string("ten_sv", 150)->nullable(false);
            $table->date("ngay_sinh")->nullable(false);
            $table->tinyInteger("phai")->nullable(false);
            $table->string("dia_chi", 300)->nullable(false);
            $table->string("sdt", 11)->nullable(false)->unique();
            $table->string("email", 50)->nullable(false)->unique();
            $table->string("anh_qr", 20)->unique();
            $table->string("ma_lop", 20);
            // $table->timestamps();

            $table->foreign("ma_lop")->references("ma_lop")->on("lop");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sinh_vien');
    }
};
