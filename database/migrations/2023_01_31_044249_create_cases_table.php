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
        Schema::create('lcs_cases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('citizen_id');
            $table->unsignedBigInteger('consultant_id');
            $table->string('title', 50);
            $table->string("description")->nullable();
            $table->tinyInteger('status')->comment("
            0 intial,
            1 inprogress,
            2 cancel,
            3 complete
            ");
            $table->string('file', 255)->nullable();
            $table->string('case_initial_date', 255)->nullable();
            $table->string('case_status_date', 255)->nullable();
            $table->string('consultant_review_comment', 255)->nullable();
            $table->string('citizen_review_comment', 255)->nullable();
            $table->string('case_code')->nullable();
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
        Schema::dropIfExists('cases');
    }
};
