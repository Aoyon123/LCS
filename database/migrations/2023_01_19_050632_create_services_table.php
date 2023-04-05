<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title', 50);
            $table->string("description")->nullable();
            $table->tinyInteger('status')->comment("
            0 inactive,
            1 active,
            ");
            $table->string('service_image')->nullable();
            $table->string('remark')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('services');
    }
};
