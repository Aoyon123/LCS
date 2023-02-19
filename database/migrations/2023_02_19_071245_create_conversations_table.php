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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('citizen_id')->nullable();
            $table->unsignedBigInteger('consultant_id')->nullable();
            $table->text("case_message")->nullable();
            $table->unsignedBigInteger('case_id')->nullable();
            $table->string('time', 255)->nullable();
            $table->boolean('seen_status')->default(0);
            $table->tinyInteger('status')->nullable();
            $table->tinyInteger('is_delete')->nullable();
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
        Schema::dropIfExists('conversations');
    }
};
