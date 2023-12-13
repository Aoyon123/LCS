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
        if (!Schema::hasTable('question_banks')) {
            Schema::create('question_banks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('service_id')->nullable();
                $table->string('question')->nullable();
                $table->mediumText("question_details")->nullable();
                $table->mediumText("answer")->nullable();
                $table->tinyInteger('status')->comment("
            0 pending,
            1 answered,
            ");
                $table->string("question_code")->nullable();
                $table->integer("created_by")->nullable();
                $table->integer("answered_by")->nullable();
                $table->integer("updated_by")->nullable();
                $table->string("case_codes")->nullable();
                $table->string("case_ids")->nullable();
                $table->timestamps();
            });
        }

        Schema::table('question_banks', function (Blueprint $table) {
            $table->string('postTar');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('question_banks');
    }
};
