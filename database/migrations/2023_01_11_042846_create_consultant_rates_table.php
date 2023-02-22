<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consultant_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('citizen_id')->comment('Users Whoose Information Is Connect With');
            $table->foreign('citizen_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('consultant_id')->comment('Users Whoose Information Is Connect With');
            $table->foreign('consultant_id')->references('id')->on('users')->onDelete('cascade');
            $table->float('rating',5,1)->default(0.0);
            $table->integer('against_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consultant_rates');
    }
};
