<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExperiencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('Users Whoose Information Is Connect With');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('institute_name');
            $table->string('address', 255)->nullable();
            $table->string('designation');
            $table->string('department');
            $table->string('start_date');
            $table->string('end_date');
            $table->string('current_working')->nullable();
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
        Schema::dropIfExists('experiences');
    }
}
