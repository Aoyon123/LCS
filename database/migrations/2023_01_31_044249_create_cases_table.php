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
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('citizen_id')->nullable();
            $table->unsignedBigInteger('consultant_id')->nullable();
            $table->string('title', 50)->nullable();
            $table->mediumText("description")->nullable();
            $table->tinyInteger('status')->comment("
            0 intial,
            1 inprogress,
            2 complete,
            3 cancel
            4 accept
            ");
            $table->string('document_file', 255)->nullable();
            $table->string('document_link', 255)->nullable();
            $table->string('case_initial_date', 255)->nullable();
            $table->string('case_status_date', 255)->nullable();
            $table->mediumText('consultant_review_comment')->nullable();
            $table->mediumText('citizen_review_comment')->nullable();
            $table->string('case_code');
            $table->text('rating')->default(0.0);
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
