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
        Schema::create('giao_vien', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string("ma_gv", 10);
            $table->primary("ma_gv");
            $table->string("ten_gv", 150)->nullable(false);
            $table->date("ngay_sinh")->nullable(false);
            $table->tinyInteger("phai")->nullable(false);
            $table->string("dia_chi", 300)->nullable(false);
            $table->string("sdt", 11)->nullable(false)->unique();
            $table->string("email", 50)->nullable(false)->unique();
            // $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('giao_vien');
    }
};
